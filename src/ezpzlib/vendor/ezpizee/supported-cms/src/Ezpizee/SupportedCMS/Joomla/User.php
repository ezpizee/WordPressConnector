<?php

namespace Ezpizee\SupportedCMS\Joomla;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\User\User as JUser;
use Joomla\CMS\String\PunycodeHelper as JStringPunycode;
use JsonSerializable;

class User implements JsonSerializable
{
    public $name = '';
    public $username = '';
    public $email = '';
    public $password = '';
    public $groups = [];

    public function jsonSerialize(): array
    {
        return [
            'name'=>$this->name,
            'username'=>$this->username,
            'email'=>$this->email,
            'password'=>$this->password,
            'groups'=>$this->groups
        ];
    }

    public static function register(User $data): JUser
    {
        if (self::isValidData($data)) {
            $user = new JUser();
            $data->email = JStringPunycode::emailToPunycode($data->email);
            $dataArray = $data->jsonSerialize();
            if ($user->bind($dataArray)) {
                $user->save();
                $userObject = self::getUserBy(['email'=>$data->email]);
                if ($userObject->id > 0) {
                    self::saveUserGroupMap((int)$userObject->id, $data->groups);
                    self::activate(['email'=>$data->email]);
                    return $userObject;
                }
                else {
                    self::delete($userObject);
                }
            }
        }
        return new JUser();
    }

    public static function activate(array $val): void {
        $db = JFactory::getDbo();
        $query = '';
        if (isset($val['email'])) {
            $query = 'email=' . $db->quote($val['email']);
        }
        else if (isset($val['username'])) {
            $query = 'username=' . $db->quote($val['username']);
        }
        else if (isset($val['id'])) {
            $query = 'id=' . $db->quote($val['id']);
        }
        if (!empty($query)) {
            $query = 'UPDATE '.'#__users SET block="0",sendEmail="1",activation="" WHERE '.$query;
            $db->setQuery($query)->execute();
        }
    }

    public static function delete($val): void {
        $userObj = new JUser();
        if ($val instanceof JUser) {
            $userObj = $val;
        }
        else if (is_array($val)) {
            $userObj = self::getUserBy($val);
        }
        else {
            $userObj->id = 0;
        }
        if ($userObj->id > 0) {
            $userObj->delete();
            /*$db = JFactory::getDbo();
            $query = 'DELETE'.' FROM #__users WHERE id=' . $db->quote($userObj->id);
            $db->setQuery($query)->execute();
            $query = 'DELETE'.' FROM #__user_usergroup_map WHERE user_id=' . $db->quote($userObj->id);
            $db->setQuery($query)->execute();
            $query = 'DELETE'.' FROM #__user_profiles WHERE user_id=' . $db->quote($userObj->id);
            $db->setQuery($query)->execute();
            $query = 'DELETE'.' FROM #__user_keys WHERE user_id=' . $db->quote($userObj->id);
            $db->setQuery($query)->execute();*/
        }
    }

    public static function getUserBy(array $val): JUser {
        $db = JFactory::getDbo();
        $query = '';
        if (isset($val['email'])) {
            $query = 'SELECT *'.' FROM #__users WHERE email=' . $db->quote($val['email']);
        }
        else if (isset($val['username'])) {
            $query = 'SELECT *'.' FROM #__users WHERE username=' . $db->quote($val['username']);
        }
        else if (isset($val['id'])) {
            $query = 'SELECT *'.' FROM #__users WHERE id=' . $db->quote($val['id']);
        }
        if (!empty($query)) {
            $data = $db->setQuery($query)->loadAssoc();
            if (!empty($data)) {
                return JFactory::getUser($data['id']);
            }
        }
        return new JUser();
    }

    public static function saveUserGroupMap(int $userId, array $groups)
    {
        $db = JFactory::getDbo();
        $values = [];
        foreach ($groups as $group) {
            $values[] = '('.$db->quote($userId).','.$db->quote($group).')';
        }
        $query = 'INSERT IGNORE'.' INTO #__user_usergroup_map(user_id,group_id) 
        VALUES'.implode(',', $values);
        $db->setQuery($query)->execute();
    }

    private static function isValidData(User $data): bool
    {
        return !empty($data->email) && !empty($data->password) &&
            !empty($data->username) && !empty($data->name) &&
            !empty($data->groups);
    }
}