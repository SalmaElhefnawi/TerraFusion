<?php
require 'app/Models/BaseModel.php';
require 'app/Models/UserModel.php';
$model = new \App\Models\UserModel();
$user = $model->getById(1);
var_dump($user);
?>