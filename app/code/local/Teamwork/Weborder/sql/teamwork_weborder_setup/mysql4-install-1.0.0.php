<?php
$installer = $this;
$installer->startSetup();

$configObject = Mage::getConfig();

$user = Mage::getModel('api/user');
$user->setWebsiteId(1);

$user->loadByUsername( (string)$configObject->getNode('teamwork_weborder/api_user/username') );

if( !$user->getData('user_id') )
{
    $roleCollection = Mage::getModel('api/roles')->getCollection();
    $roleName = (string)$configObject->getNode('teamwork_weborder/api_user/role');
    $roleCollection->addFieldToFilter('role_name', $roleName);
    $roleCollection->addFieldToFilter('role_type', 'G');
    if ($roleCollection->count())
    {
        $role = $roleCollection->getFirstItem();
    }
    else
    {
        $role = Mage::getModel('api/roles')
            ->setName((string)$configObject->getNode('teamwork_weborder/api_user/role'))
            ->setPid(false)
        ->setRoleType('G')->save();
    }

    Mage::getModel("api/rules")
        ->setRoleId($role->getId())
        ->setResources(array('all'))
    ->saveRel();

    $user->setData(array(
        'username'              => (string)$configObject->getNode('teamwork_weborder/api_user/username'),
        'firstname'             => (string)$configObject->getNode('default/trans_email/ident_general/name'),
        'lastname'              => '',
        'email'                 => (string)$configObject->getNode('default/trans_email/ident_general/email'),
        'api_key'               => (string)$configObject->getNode('teamwork_weborder/api_user/default_key'),
        'api_key_confirmation'  => (string)$configObject->getNode('teamwork_weborder/api_user/default_key'),
        'is_active'             => 1,
        'user_roles'            => '',
        'assigned_user_role'    => '',
        'role_name'             => $roleName,
        'roles'                 => array($role->getId())
    ));
    $user->save()->load($user->getId());

    $user->setRoleIds(array($role->getId()))
       ->setRoleUserId($user->getUserId())
    ->saveRelations();
}
$installer->endSetup();