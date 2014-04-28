<?php
/**
* Route Maps
* Notes: needed '$router = new Router();' before and '$router->run();' after.
*
* @author     Xiaoyu Tai @ Beijing, 2014.4.25
* @copyright  Copyright (c), 2014 Xiaoyu Tai
* @license    MIT license (see /mit/)
*
* Thanks Van Damme(@Bramus) for this amazing PHP Router
* <https://github.com/bramus/router>
*/
/**
* === 404 Pages ======================================================
* And it looks like this: 
*   404 Not Found
*        :(
* ====================================================================
*/
$router->set404(function() {
  header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
  echo "<html><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"w"
     . "idth=device-width, initial-scale=1.0\"><title>404 Not Found</title></he"
     . "ad><body><center style=\"margin-top: 60px; font-size:30px; color:#999; "
     . "font-weight:100;\">404 Not Found</center><center style=\"margin-top: 20"
     . "px; font-size:100px; color:#333; font-weight:100;\">: (</center></body>";
});
/**
* === Before Routing =================================================
* set authorization for regular users and admins.
*   Logic is here:
*     Everyone:    only [/mit/, /login/, /admin/login/]
*     Auth Uesrs:  [/.*], not include [/admin/.*, /lgoin/]
*     Auth Admins: only [/admin/.*], not include [/admin/login/]
*
* Notes: need a exit(); to prevent proceing more than we need
* ====================================================================
*/
$router->before("GET|POST", "/(.*)", function($url) {
  if(!isset($_SESSION["xg_user_type"]) && $url != "login" && !preg_match("/admin/", $url) && $url != "mit"){
    header("location: /login/");
    exit();
  }elseif(isset($_SESSION["xg_user_type"])){
    if($_SESSION["xg_user_type"] == "admins" && !preg_match("/admin/", $url) && $url != "mit"){
      header("location: /admin/");
      exit();
    }
  }
});
$router->before("GET|POST", "/admin(.*)", function($url) {
  if(!isset($_SESSION["xg_user_type"]) && $url != "login"){
    header("location: /admin/login/");
    exit();
  }elseif(isset($_SESSION["xg_user_type"])){
    if($_SESSION["xg_user_type"] == "users"){
      header("location: /");
      exit();
    }elseif($url == "login"){
      header("location: /admin/");
      exit();
    }
  }
});
/**
* === For Everyone without Auth ======================================
* [/mit/, /login/, /admin/login/]
*   GET  - /mit/         MIT License
*   GET  - /login/       User Login
*   GET  - /admin/login/ Admin Login
*   POST - /login/       POST Method for User Login
*   POST - /admin/login/ POST Method for Admin Login
*
* Notes: 
*   To switch /login/ <==> /admin/login:
*   press ":" key when you are in login pages.                    : )
* ====================================================================
*/
$router->get("/mit", function(){
  $View = new \View\View(null, null, null);
  $View->show("mit");
});
$router->get("/login", function(){
  $View = new \View\View("选课指南", "1", null);
  $View->show("login");
});
$router->post("/login", function(){
  $ModuleUser = new \Module\ModuleUser();
  $ModuleUser->login($_POST["username"], $_POST["password"]);
  header("location: /");
});
$router->get("/admin/login", function(){
  $View = new \View\View("后台登陆", "3", null);
  $View->show("admin_login");
});
$router->post("/admin/login", function(){
  $ModuleAdmin = new \Module\ModuleAdmin();
  $ModuleAdmin->login($_POST["username"], $_POST["password"]);
  header("location: /admin/");
});
/**
* === For Auth Users =================================================
* [/.*], not include [/admin/.*, /lgoin/]
*   GET  - /             Home Page - 所有课程
*   POST - /select/      AJAX - select/unselect
*   GET  - /mine/        Mine Page - 我的课程
*   GET  - /logout/      Logout --> unAuth
*   GET  - /plan/        Plan - 教学计划
*   GET  - /message/     Message - 留言板
*   GET  - /password/    Password - 修改密码
*   POST - /password/    POST Method for Change Password
*   GET  - /subject/\d*  Subject info
*   GET  - /user/\w*     User info
*   GET  - /user/?q=\w*  Search User info
* ====================================================================
*/
$router->get("/", function(){
  $ModuleUser = new \Module\ModuleUser();
  $View = new \View\View("所有课程 &middot; 选课指南", "0", "1");
  $View->setData($ModuleUser->getData("home"));
  $View->show("home");
});
$router->post("/select", function(){
  $ModuleUser = new \Module\ModuleUser();
  $ModuleUser->ajax_change($_POST['sid'], $_POST['option']);
});
$router->get("/mine", function(){
  $ModuleUser = new \Module\ModuleUser();
  $View = new \View\View("我的课程 &middot; 选课指南", "0", "2");
  $View->setData($ModuleUser->getData("mine"));
  $View->show("mine");
});
$router->get("/logout", function(){
  $ModuleUser = new \Module\ModuleUser();
  $ModuleUser::logout();
  header("location: /login/");
});
$router->get("/plan", function(){
  $ModuleUser = new \Module\ModuleUser();
  $View = new \View\View("教学计划 &middot; 选课指南", "0", "3");
  $View->setData($ModuleUser->getData("plan"));
  $View->show("plan");
});
$router->get("/message", function(){
  $View = new \View\View("留言板 &middot; 选课指南", "0", "4");
  $View->show("message");
});
$router->get("/password", function(){
  $View = new \View\View("修改密码 &middot; 选课指南", "0", "5");
  $View->show("password");
});
$router->post("/password", function(){
  $ModuleUser = new \Module\ModuleUser();
  $ModuleUser->newPassword($_POST["password"]);
  header("location: /logout/");
});
$router->get('/subject/(\d*)', function($subject_id){
  $ModuleUser = new \Module\ModuleUser();
  $data = $ModuleUser->getSubjectData($subject_id);
  $View = new \View\View($data["subject"]["subject_name"]." &middot; 选课指南", "0", null);
  $View->setData($data);
  $View->show($data["viewname"]);
});
$router->get('/user/(\w*)', function($user_id){
  $ModuleUser = new \Module\ModuleUser();
  $data = $ModuleUser->getUserData($user_id);
  $View = new \View\View($data["_title"]." &middot; 选课指南", "0", null);
  $View->setData($data);
  $View->show($data["viewname"]);
});
$router->get('/user/', function(){
  if(isset($_GET["q"]) && preg_match("/\w*/", $_GET["q"])){
    header("location: /user/".$_GET["q"]);
  }else{
    header("location: /mine/");
  }
});
/**
* === For Auth Admins ================================================
* [/admin/.*]
*   GET  - /admin/                 Admin Page - 后台管理
*   GET  - /admin/logout/          Logout --> unAuth
*   GET  - /admin/password/        Password - 修改密码
*   POST - /admin/password/        POST Method for Change Password
*   GET  - /admin/user/            User - 学生管理
*   GET  - /admin/user/?q=.*       User Search
*   GET  - /admin/user/\w*         User Info
*   GET  - /admin/subject/         Subject - 课程管理
*   GET  - /admin/user/?q=.*       Subject Search
*   GET  - /admin/subject/\d*      Subject Info
*   GET  - /admin/major/           Major - 教学计划管理
*   GET  - /admin/major/\d*        Major Info
*   GET  - /admin/import/          Import - 导入向导
* ====================================================================
*/
$router->get("/admin/logout", function(){
  $ModuleAdmin = new \Module\ModuleAdmin();
  $ModuleAdmin::logout();
  header("location: /admin/login/");
});
$router->get("/admin/", function(){
  $View = new \View\View("面板 &middot; 选课指南后台管理", "2", "1");
  $ModuleAdmin = new \Module\ModuleAdmin();
  $View->setData($ModuleAdmin->getData("admin_home"));
  $View->show("admin_home");
});
$router->get("/admin/password", function(){
  $View = new \View\View("修改密码 &middot; 选课指南后台管理", "2", "6");
  $View->show("admin_password");
});
$router->post("/admin/password", function(){
  $ModuleAdmin = new \Module\ModuleAdmin();
  $ModuleAdmin->newPassword($_POST["password"]);
  header("location: /admin/logout/");
});
$router->get("/admin/user", function(){
  if(isset($_GET["q"]) && preg_match("/\w*/", $_GET["q"])){
    header("location: /admin/user/".$_GET["q"]);
  }else{
    $ModuleAdmin = new \Module\ModuleAdmin();
    $total_page_num = $ModuleAdmin->getTotalPageNum("user");
    $page_current_num = 1;
    if(isset($_GET["p"])){
      if(preg_match("/\d*/", $_GET["p"]) && ($_GET["p"] > 0) && (($total_page_num - $_GET["p"]) >= 0)){
        $page_current_num = $_GET["p"];
      }
    }
    $data = $ModuleAdmin->getUserMainData($page_current_num);
    $View = new \View\View("用户管理 &middot; 选课指南后台管理", "2", "2");
    $View->setData($data);
    $View->show("admin_user");
  }
});
$router->get('/admin/user/(\d*)', function($user_id){
  $ModuleAdmin = new \Module\ModuleAdmin();
  $data = $ModuleAdmin->getUserData($user_id);
  $View = new \View\View($data["_title"]." &middot; 选课指南后台管理", "0", null);
  $View->setData($data);
  $View->show($data["viewname"]);
});
$router->get("/admin/subject", function(){
  if(isset($_GET["q"]) && preg_match("/\d*/", $_GET["q"])){
    header("location: /admin/subject/".$_GET["q"]);
  }else{
    $ModuleAdmin = new \Module\ModuleAdmin();
    $total_page_num = $ModuleAdmin->getTotalPageNum("subject");
    $page_current_num = 1;
    if(isset($_GET["p"])){
      if(preg_match("/\d*/", $_GET["p"]) && ($_GET["p"] > 0) && (($total_page_num - $_GET["p"]) >= 0)){
        $page_current_num = $_GET["p"];
      }
    }
    $data = $ModuleAdmin->getSubjectMainData($page_current_num);
    $View = new \View\View("课程管理 &middot; 选课指南后台管理", "2", "3");
    $View->setData($data);
    $View->show("admin_subject");
  }
});
$router->get('/admin/subject/(\d*)', function($subject_id){
  $ModuleAdmin = new \Module\ModuleAdmin();
  $data = $ModuleAdmin->getSubjectData($subject_id);
  $View = new \View\View($data["subject"]["subject_name"]." &middot; 选课指南后台管理", "0", null);
  $View->setData($data);
  $View->show($data["viewname"]);
});
$router->get("/admin/major", function(){
  $View = new \View\View("教学计划管理 &middot; 选课指南后台管理", "2", "4");
  $View->show("admin_major");
});
$router->get("/admin/import", function(){
  $View = new \View\View("导入向导 &middot; 选课指南后台管理", "2", "5");
  $View->show("admin_import");
});

/**
* ====================================================================
* Testing routes
* ====================================================================
*/
/*
$router->get("/admin/a", function(){
  echo "admin/a : )<br/>";
});
$router->get("/a", function(){
  echo "a : )<br/>";
});
*/