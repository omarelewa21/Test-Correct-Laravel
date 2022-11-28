<?php
//  File: config/route_perms.php
//  contains the route.name role mappings.
//  Format: ['route.name' => ['role1', 'role2', ['or' => ['role3', 'role4']]], ['or' => ['role6', 'role5', 'or' => ['role5.2', 'role5.2']]]]]

return [
    '*'   => ['or' => ['Teacher', 'Invigilator', 'Student', 'Administrator', 'Account manager', 'School manager', 'School management', 'Tech administrator', 'Support']],
    ''
];