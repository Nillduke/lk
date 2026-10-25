<?php

return [
    'reports' => ['table' => 'reports', 'permission' => 'view_reports', 'write' => 'edit_reports', 'label' => 'Reports', 'fields' => ['title', 'slug', 'category_id', 'publisher_id', 'keyword', 'description', 'region', 'pages', 'pub_date', 'status', 'key_info', 'meta_title', 'meta_description', 'meta_keywords']],
    'categories' => ['table' => 'categories', 'permission' => 'manage_categories', 'label' => 'Categories', 'fields' => ['name', 'slug', 'meta_title', 'meta_description']],
    'publishers' => ['table' => 'publishers', 'permission' => 'manage_publishers', 'label' => 'Publishers', 'fields' => ['name', 'slug', 'contact_name', 'contact_email', 'description']],
    'news' => ['table' => 'news', 'permission' => 'manage_news', 'label' => 'Industry news', 'fields' => ['headline', 'slug', 'category_id', 'description', 'content', 'featured_img', 'page_title', 'meta_description', 'meta_keywords', 'status']],
    'users' => ['table' => 'users', 'permission' => 'view_users', 'write' => 'update_user', 'label' => 'Users', 'fields' => ['name', 'username', 'email', 'password', 'status', 'is_email_verified']],
    'enquiries' => ['table' => 'reports_enquiries', 'permission' => 'manage_enquiries', 'label' => 'Enquiries', 'fields' => ['report_id', 'full_name', 'email_id', 'contact_no', 'company', 'role', 'country', 'message', 'type', 'status']],
    'checkouts' => ['table' => 'checkouts', 'permission' => 'manage_checkouts', 'label' => 'Orders and checkouts', 'fields' => ['report_id', 'report_license_id', 'order_number', 'name', 'email', 'contact_no', 'company', 'country', 'address', 'message', 'license_type', 'total_amount', 'amt_tobe_paid', 'currency', 'payment_by', 'payment_status', 'payment_info', 'status', 'is_active']],
    'unsubscribed-users' => ['table' => 'unsubscribed_list', 'permission' => 'unsubcribed_list', 'label' => 'Unsubscribed users', 'fields' => ['email_id', 'feedback']],
    'comments' => ['table' => 'comments', 'permission' => 'manage_comments', 'label' => 'Comments', 'fields' => ['user_id', 'comment_author', 'comment_author_email', 'comment_content', 'comment_date', 'comment_approved', 'comment_parent', 'commentable_id', 'commentable_type']],
    'bugs' => ['table' => 'lr_bug_reports', 'permission' => 'manage_bugs', 'label' => 'Bug reports', 'fields' => ['title', 'description', 'status']],
    'pages' => ['table' => 'lr_pages', 'permission' => 'manage_cms', 'label' => 'Website pages', 'fields' => ['slug', 'title', 'content', 'status']],
    'roles' => ['table' => 'rbac_roles', 'permission' => 'manage_access', 'label' => 'Roles', 'pk' => 'ID', 'fields' => ['Title', 'Description']],
    'permissions' => ['table' => 'rbac_permissions', 'permission' => 'manage_access', 'label' => 'Permissions', 'pk' => 'ID', 'fields' => ['Title', 'Description']],
];
