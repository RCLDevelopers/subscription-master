<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Subscription\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Pi\Application\Installer\SqlSchema;
use Laminas\EventManager\Event;

class Update extends BasicUpdate
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.pre', array($this, 'updateSchema'));
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function updateSchema(Event $e)
    {


        $moduleVersion = $e->getParam('version');

        // Set item model
        $peopleModel = Pi::model('people', $this->module);
        $peopleTable = $peopleModel->getTable();
        $peopleAdapter = $peopleModel->getAdapter();

        $userAccountModel= Pi::model('account', 'user');
        $userAccountTable = $userAccountModel->getTable();
        $userAccountAdapter = $userAccountModel->getAdapter();

        $userProfileModel= Pi::model('profile', 'user');
        $userProfileTable = $userProfileModel->getTable();
        $userProfileAdapter = $userProfileModel->getAdapter();


        // Update to version 0.0.4
        if (version_compare($moduleVersion, '0.0.4', '<')) {
            // Alter table : ADD difficulty
            $sql = sprintf("ALTER TABLE %s CHANGE `email` `email` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL", $peopleTable);
            try {
                $peopleAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s CHANGE `mobile` `mobile` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL", $peopleTable);
            try {
                $peopleAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }
        if (version_compare($moduleVersion, '0.3.0', '<')) {


            $sql = sprintf("ALTER TABLE %s CHANGE `email` `email` VARCHAR(64) NULL DEFAULT NULL;", $peopleTable);
            try {
                $peopleAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s CHANGE `mobile` `mobile` VARCHAR(16) NULL DEFAULT NULL;", $peopleTable);
            try {
                $peopleAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("ALTER TABLE %s DROP INDEX mobile;", $peopleTable);
            try {
                $peopleAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("UPDATE %s people JOIN %s account on  account.id = people.uid set people.status = 0 where account.active = 0;", $peopleTable, $userAccountTable);
            try {
                $peopleAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }

            $sql = sprintf("UPDATE %s people JOIN %s account on  account.id = people.uid JOIN %s profile on  profile.uid = account.id  set people.first_name = profile.first_name, people.last_name = profile.last_name, people.mobile= profile.mobile, people.email= account.email;", $peopleTable, $userAccountTable, $userProfileTable);
            try {
                $peopleAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }

        if (version_compare($moduleVersion, '0.4.0', '<')) {
            $sql = sprintf("ALTER TABLE %s ADD `time_update`  INT(10) UNSIGNED NOT NULL DEFAULT '0'", $peopleTable);
            try {
                $peopleAdapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status' => false,
                    'message' => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));
                return false;
            }
        }
        return true;
    }
}