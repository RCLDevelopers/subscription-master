<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Subscription\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Module\Subscription\Form\ExportForm;
use Module\Subscription\Form\ExportFilter;
use Laminas\Db\Sql\Predicate\Expression;

class PeopleController extends ActionController
{
    public function indexAction()
    {
        // Get page
        $page = $this->params('page', 1);
        // Get info
        $list = array();
        $order = array('time_join DESC', 'id DESC');
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit = intval($this->config('admin_perpage'));
        $select = $this->getModel('people')->select()->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('people')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
            $list[$row->id]['isUser'] = ($row->uid > 0) ? 1 : 0;
            if ($row->uid > 0) {
                $list[$row->id]['user'] = Pi::api('user', 'subscription')->getUserInformation($row->uid);
            }
        }
        // Set paginator
        $count = array('count' => new Expression('count(*)'));
        $select = $this->getModel('people')->select()->columns($count);
        $count = $this->getModel('people')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router' => $this->getEvent()->getRouter(),
            'route' => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params' => array_filter(array(
                'module' => $this->getModule(),
                'controller' => 'people',
                'action' => 'index',
            )),
        ));
        // Set view
        $this->view()->setTemplate('people-index');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
    }

    public function exportAction()
    {
        // Get inf0
        $module = $this->params('module');
        $file = $this->params('file');
        $start = $this->params('start', 0);
        $count = $this->params('count');
        $complete = $this->params('complete', 0);
        $confirm = $this->params('confirm', 0);
        $startUser = $this->params('start_user', 0);
        $includeUser = $this->params('include_user', 0);

        // Set path
        $path = Pi::path('upload/subscription');
        if (!Pi::service('file')->exists($path . '/index.html')) {
            Pi::service('file')->copy(
                Pi::path('upload/index.html'),
                Pi::path('upload/subscription/index.html')
            );
        }

        // Get config
        $config = Pi::service('registry')->config->read($module);
        // Check request
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form = new ExportForm('export');
            $form->setInputFilter(new ExportFilter(array()));
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $url = array(
                    'action' => 'export',
                    'include_user' => $values['include_user'],
                    'start_user' => 0,
                    'confirm' => 1,
                    'file' => sprintf('subscription-%s-%s', date('Y-m-d-H-i-s'), rand(100, 999)),
                );
                return $this->jump($url);
            } else {
                $message = __('Not valid');
                $url = array(
                    'action' => 'export',
                );
                return $this->jump($url, $message);
            }
        } elseif ($confirm == 1) {

            // Set file
            Pi::service('audit')->attach('subscription-export', array(
                'file'   => Pi::path(sprintf('upload/subscription/%s.csv', $file)),
                'format' => 'csv',
            ));

            $order = array('id ASC');
            $whereUser = array();
            $whereUser['id > ?'] = $start;
            $wherePeople = array();
            $wherePeople['id > ?'] = $start;

            // Get count
            if (!$count) {
                $columns = array('count' => new Expression('count(*)'));
                if ($includeUser) {
                    $select = Pi::Model('user_account')->select()->columns($columns)->where($whereUser);
                    $count = Pi::Model('user_account')->selectWith($select)->current()->count + $count;
                } else {
                    $select = $this->getModel('people')->select()->columns($columns)->where($wherePeople);
                    $count = $this->getModel('people')->selectWith($select)->current()->count;
                }
            }

            if ($includeUser) {
                // Get from system user table
                $peopleTable = Pi::model('people', 'subscription')->getTable();
                $accountTable = Pi::model('user_account')->getTable();
                $profileTable = Pi::model('profile', 'user')->getTable();
                $select = Pi::db()->select();


                $select->from(array('user' => $accountTable))
                    ->join(array('profile' => $profileTable), 'user.id = profile.uid', array(
                        'first_name' => 'first_name',
                        'last_name' => 'last_name',
                        'mobile' => 'mobile',
                    ), 'left')
                    ->join(array('people' => $peopleTable), 'people.uid = user.id', array('newsletter', 'time_join', 'time_update'), 'left');


                $rowset = Pi::db()->query($select);

                // Set key
                if ($complete == 0) {
                    $keys = array(
                        'uid',
                        'status',
                        'first_name',
                        'last_name',
                        'email',
                        'mobile',
                        'time_join',
                        'time_update',
                        'newsletter'
                    );
                    Pi::service('audit')->log('subscription-export', $keys);
                }

                // Make list
                foreach ($rowset as $user) {

                    // Set to csv
                    Pi::service('audit')->log('subscription-export', array(
                        'uid'  => $user['id'],
                        'status' => $user['active'] ? __('Enabled') : __('Disabled'),
                        'first_name' => !empty($user['first_name']) ? $user['first_name'] : $user['name'],
                        'last_name' => !empty($user['last_name']) ? $user['last_name'] : '',
                        'email' => $user['email'],
                        'mobile' => $user['mobile'],
                        'time_join' => $user['time_join'] ? (_date($user['time_join']) . date(' H:i', $user['time_join'])) : null,
                        'time_update' => $user['time_update'] ? (_date($user['time_update']) . date(' H:i', $user['time_update'])) : null,
                        'newsletter' => $user['newsletter'] ? $user['newsletter'] : 0
                    ));

                    // Set extra
                    $lastId = $user['id'];
                    $complete++;
                }
            } else {
                // Get from module people table
                $select = $this->getModel('people')->select()->where($wherePeople)->order($order)->limit(50);
                $rowset = $this->getModel('people')->selectWith($select);

                // Make list
                $peopleCont = 0;
                foreach ($rowset as $row) {
                    $people = $row->toArray();

                    // Set key
                    if ($complete == 0) {
                        $keys = array(
                            'uid',
                            'status',
                            'first_name',
                            'last_name',
                            'email',
                            'mobile',
                            'time_join',
                            'time_update',
                            'newsletter'
                        );
                        Pi::service('audit')->log('subscription-export', $keys);
                    }

                    // Set to csv
                    Pi::service('audit')->log('subscription-export', array(
                        'uid'  => $people['uid'],
                        'status' => $people['status'] ? __('Enabled') : __('Disabled'),
                        'first_name' => $people['first_name'],
                        'last_name' => $people['last_name'],
                        'email' => $people['email'],
                        'mobile' => $people['mobile'],
                        'time_join' => _date($people['time_join']) . date(' H:i', $people['time_join']),
                        'time_update' => $people['time_update'] ? (_date($people['time_update']) . date(' H:i', $people['time_update'])) : null,
                        'newsletter' => $people['newsletter']
                    ));

                    // Set extra
                    $lastId = $people['id'];
                    $complete++;
                    $peopleCont++;
                }
                if ($peopleCont == 0) {
                    $startUser = 1;
                }
            }



            // Set complete
            $percent = (100 * $complete) / $count;
            // Set next url
            if ($complete >= $count) {
                $nextUrl = '';
                $downloadUrl = sprintf('%s?upload/subscription/%s.csv', Pi::url('www/script/download.php'), $file);
            } else {
                $nextUrl = Pi::url($this->url('', array(
                    'action' => 'export',
                    'start' => $lastId,
                    'count' => $count,
                    'complete' => $complete,
                    'confirm' => $confirm,
                    'file' => $file,
                    'start_user' => $startUser,
                    'include_user' => $includeUser,

                )));
                $downloadUrl = '';
            }

            $info = array(
                'start' => $lastId,
                'count' => $count,
                'complete' => $complete,
                'percent' => $percent,
                'nextUrl' => $nextUrl,
                'downloadUrl' => $downloadUrl,
            );

            $percent = ($percent > 99 && $percent < 100) ? (intval($percent) + 1) : intval($percent);

        } else {
            // Set info
            $info = array();
            $percent = 0;
            $nextUrl = '';
            $downloadUrl = '';
            // Set form
            $form = new ExportForm('export');
            // Set filter
            $filter = function ($fileinfo) {
                if (!$fileinfo->isFile()) {
                    return false;
                }
                $filename = $fileinfo->getFilename();
                if ('index.html' == $filename) {
                    return false;
                }
                return $filename;
            };
            // Get file list
            $fileList = Pi::service('file')->getList($path, $filter);
            // Set view
            $this->view()->assign('form', $form);
            $this->view()->assign('fileList', $fileList);
        }
        // Set view
        $this->view()->setTemplate('people-export');
        $this->view()->assign('config', $config);
        $this->view()->assign('nextUrl', $nextUrl);
        $this->view()->assign('downloadUrl', $downloadUrl);
        $this->view()->assign('percent', $percent);
        $this->view()->assign('info', $info);
        $this->view()->assign('confirm', $confirm);
    }
}