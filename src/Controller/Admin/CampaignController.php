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
use Pi\Filter;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Pi\File\Transfer\Upload;
use Module\Subscription\Form\CampaignForm;
use Module\Subscription\Form\CampaignFilter;
use Laminas\Db\Sql\Predicate\Expression;

class CampaignController extends ActionController
{
    public function indexAction()
    {
        // Get page
        $page = $this->params('page', 1);
        $module = $this->params('module');
        // Get info
        $list = array();
        $order = array('time_create DESC', 'id DESC');
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        $limit = intval($this->config('admin_perpage'));
        $select = $this->getModel('campaign')->select()->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('campaign')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $list[$row->id] = $row->toArray();
            $list[$row->id]['url'] = Pi::url($this->url('default', array(
                'module' => $module,
                'controller' => 'index',
                'action' => 'index',
                'slug' => $row->slug,
            )));
            $list[$row->id]['time'] = sprintf(__('From %s to %s'),
                _date($row->time_start),
                _date($row->time_end));
            $list[$row->id]['isExpire'] = (time() > $row->time_end) ? 1 : 0;
        }
        // Set paginator
        $count = array('count' => new Expression('count(*)'));
        $select = $this->getModel('campaign')->select()->columns($count);
        $count = $this->getModel('campaign')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router' => $this->getEvent()->getRouter(),
            'route' => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params' => array_filter(array(
                'module' => $this->getModule(),
                'controller' => 'campaign',
                'action' => 'index',
            )),
        ));
        // Set view
        $this->view()->setTemplate('campaign-index');
        $this->view()->assign('list', $list);
        $this->view()->assign('paginator', $paginator);
    }

    public function updateAction()
    {
        // Get id
        $id = $this->params('id');
        $module = $this->params('module');
        $option = array();
        // Find campaign
        if ($id) {
            $campaign = $this->getModel('campaign')->find($id)->toArray();
            if ($campaign['image']) {
                $campaign['thumbUrl'] = sprintf('upload/%s/thumb/%s/%s', $this->config('image_path'), $campaign['path'], $campaign['image']);
                $option['thumbUrl'] = Pi::url($campaign['thumbUrl']);
                $option['removeUrl'] = $this->url('', array('action' => 'remove', 'id' => $campaign['id']));
            }
        }
        // Set form
        $form = new CampaignForm('campaign', $option);
        $form->setAttribute('enctype', 'multipart/form-data');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $file = $this->request->getFiles();
            // Set slug
            $slug = ($data['slug']) ? $data['slug'] : $data['title'];
            $filter = new Filter\Slug;
            $data['slug'] = $filter($slug);
            // Form filter
            $form->setInputFilter(new CampaignFilter($option));
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // upload image
                if (!empty($file['image']['name'])) {
                    // Set upload path
                    $values['path'] = sprintf('%s/%s', date('Y'), date('m'));
                    $originalPath = Pi::path(sprintf('upload/%s/original/%s', $this->config('image_path'), $values['path']));
                    // Image name
                    $imageName = Pi::api('image', 'subscription')->rename($file['image']['name'], $this->ImageCampaignPrefix, $values['path']);
                    // Upload
                    $uploader = new Upload;
                    $uploader->setDestination($originalPath);
                    $uploader->setRename($imageName);
                    $uploader->setExtension($this->config('image_extension'));
                    $uploader->setSize($this->config('image_size'));
                    if ($uploader->isValid()) {
                        $uploader->receive();
                        // Get image name
                        $values['image'] = $uploader->getUploaded('image');
                        // process image
                        Pi::api('image', 'subscription')->process($values['image'], $values['path']);
                    } else {
                        $this->jump(array('action' => 'update'), __('Problem in upload image. please try again'));
                    }
                } elseif (!isset($values['image'])) {
                    $values['image'] = '';
                }
                // Set seo_title
                $title = ($values['seo_title']) ? $values['seo_title'] : $values['title'];
                $filter = new Filter\HeadTitle;
                $values['seo_title'] = $filter($title);
                // Set seo_keywords
                $keywords = ($values['seo_keywords']) ? $values['seo_keywords'] : $values['title'];
                $filter = new Filter\HeadKeywords;
                $filter->setOptions(array(
                    'force_replace_space' => (bool)$this->config('force_replace_space'),
                ));
                $values['seo_keywords'] = $filter($keywords);
                // Set seo_description
                $description = ($values['seo_description']) ? $values['seo_description'] : $values['title'];
                $filter = new Filter\HeadDescription;
                $values['seo_description'] = $filter($description);
                // Set time
                if (empty($values['id'])) {
                    $values['time_create'] = time();
                }
                $values['time_start'] = strtotime($values['time_start']);
                $values['time_end'] = strtotime($values['time_end']);
                // Save values
                if (!empty($values['id'])) {
                    $row = $this->getModel('campaign')->find($values['id']);
                } else {
                    $row = $this->getModel('campaign')->createRow();
                }
                $row->assign($values);
                $row->save();
                // Add log
                $message = __('Campaign data saved successfully.');
                $this->jump(array('action' => 'index'), $message);
            }
        } else {
            if ($id) {
                $campaign['time_start'] = date("Y-m-d H:i:s", $campaign['time_start']);
                $campaign['time_end'] = date("Y-m-d H:i:s", $campaign['time_end']);

            } else {
                $campaign = array();
                $campaign['time_start'] = date("Y-m-d H:i:s", time());
                $campaign['time_end'] = date("Y-m-d H:i:s", strtotime("+3 month"));
            }
            $form->setData($campaign);
        }
        // Set view
        $this->view()->setTemplate('campaign-update');
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add campaign'));
    }

    public function removeAction()
    {
        // Get id and status
        $id = $this->params('id');
        // set campaign
        $campaign = $this->getModel('campaign')->find($id);
        // Check
        if ($campaign && !empty($id)) {
            // clear DB
            $campaign->image = '';
            $campaign->path = '';
            // Save
            if ($campaign->save()) {
                $message = sprintf(__('Image of %s removed'), $campaign->title);
                $status = 1;
            } else {
                $message = __('Image not remove');
                $status = 0;
            }
        } else {
            $message = __('Please select campaign');
            $status = 0;
        }
        return array(
            'status' => $status,
            'message' => $message,
        );
    }
}