<?php
set_time_limit(0);
/**
 *
 * Copyright 2005-2006 The Apache Software Foundation
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/* vim: set expandtab tabstop=3 shiftwidth=3: */
use FuseSource\Stomp\Stomp;
use FuseSource\Stomp\Message\Map;
use FuseSource\Stomp\Message;

include_once 'FuseSource/Stomp/Stomp.php';
include_once 'FuseSource/Stomp/Message/Map.php';
include_once 'FuseSource/Stomp/Message.php';

$stomp = new Stomp("tcp://114.215.29.139:61617");   
$link = $stomp->connect("lxsoft", "lxtest");

/****************模拟登陆TT消息服务器start******************/
$url = "http://127.0.0.1/tt/auth/login";
$post = array("submit" => "submit","account" => "hh","password" => "hh");
$cookie = './a.txt';
//模拟登录
login_post($url, $cookie, $post);
/****************模拟登陆TT消息服务器 end******************/


if (!$link) {    
    die("Can't connect MQ !!");    
} else {    
    $stomp->setReadTimeout(1);
    $stomp->subscribe("/queue/test.sns", array("transformation" => "jms-map-json"));
    // receive a message from the queue
    $msg = $stomp->readFrame();
    // do what you want with the message
    if ( $msg != null) {
        //echo "Received message : ";
        print_r($msg->body);
        $conn = mysql_connect('localhost', 'root', '');
        mysql_select_db('tongbu', $conn);
        mysql_query("SET NAMES UTF8");
        $json = $msg->body;
        $ar = json_decode($json,true);

        $queue = $ar['map']['entry'][5]['string'][1];//队列名字
        $rand = $ar['map']['entry'][4]['string'][1];//随机数
        $time = $ar['map']['entry'][2]['long'];//时间戳
        $sign = $ar['map']['entry'][0]['string'][1];//签名
        $datas = $ar['map']['entry'][1]['string'][1];//data 数据也
        $accesskey = "BENmjo";//ACCESSKEY
        if (md5($queue.$accesskey.$rand.$datas.$time) == $sign) {//MD5加密后与签名比较
          $arr = json_decode($ar['map']['entry'][1]['string'][1],true);
          $tableName = $arr['objectKey'];//表名
          $tableData = $arr['dataList'];//数据
          $num = count($tableData);//多少条数据

          if ($tableName == 'change_state_org_department') {//对应org_department表

            if ($arr['event'] == "INSERT") {//插入数据
              for ($i=0; $i < $num; $i++) {
                $str = "('". $tableData[$i]["syncId"]."','".$tableData[$i]["schoolId"]."','".$tableData[$i]["parentId"]."','".$tableData[$i]["no"]."','".$tableData[$i]["name"]."','".$tableData[$i]["masterId"]."','".$tableData[$i]["masterName"]."'),".$str;
              }
              $str = trim($str,",");
              $sql = "INSERT INTO ocenter_org_department (id,school_id,parent_id,no,name,master_id,master_name) VALUES ".$str;
              // echo $sql;
              mysql_query($sql);
            }

            if ($arr['event'] == "MODIFY") {//修改数据
              for ($i=0; $i < $num; $i++) {
                $sum = mysql_query("SELECT count(*) FROM ocenter_org_department WHERE id = '".$tableData[$i]['syncId']."'");//查看库中是否有这条数据
                if (mysql_result($sum,0) == 0) {//找不到对应数据（执行insert）
                  $sql = "INSERT INTO ocenter_org_department (id,school_id,parent_id,no,name,master_id,master_name) VALUES ('". $tableData[$i]["syncId"]."','".$tableData[$i]["schoolId"]."','".$tableData[$i]["parentId"]."','".$tableData[$i]["no"]."','".$tableData[$i]["name"]."','".$tableData[$i]["masterId"]."','".$tableData[$i]["masterName"]."')";
                  mysql_query($sql);
                } else {//找到对应数据(执行update)
                  $sql = "UPDATE ocenter_org_department SET school_id ='".$tableData[$i]["schoolId"]."',parent_id='".$tableData[$i]["parentId"]."',no='".$tableData[$i]["no"]."',name='".$tableData[$i]["name"]."',master_id='".$tableData[$i]["masterId"]."',master_name='".$tableData[$i]["masterName"]."'"." WHERE id ='".$tableData[$i]["syncId"]."'";
                  mysql_query($sql);
                }
              }
            }

            if ($arr['event'] == "DELETE") {//删除数据
              for ($i=0; $i < $num; $i++) {
                $sql = "DELETE FROM ocenter_org_department WHERE id = '".$tableData[$i]["syncId"]."'";
                mysql_query($sql);
              }
            }
          }


          if ($tableName == 'change_state_org_grade_class') {//对应org_grade_class表

            if ($arr['event'] == "INSERT") {//插入数据
              for ($i=0; $i < $num; $i++) {
                $str = "('". $tableData[$i]["syncId"]."','".$tableData[$i]["schoolId"]."','".$tableData[$i]["name"]."','".$tableData[$i]["nj"]."','".$tableData[$i]["masterName"]."'),".$str;
              }
              $str = trim($str,",");
              $sql = "INSERT INTO ocenter_org_grade_class (id,school_id,name,nj,master_name) VALUES ".$str;
              // echo $sql;
              mysql_query($sql);
            }

            if ($arr['event'] == "MODIFY") {//修改数据
              for ($i=0; $i < $num; $i++) {
                $sum = mysql_query("SELECT count(*) FROM ocenter_org_grade_class WHERE id = '".$tableData[$i]['syncId']."'");//查看库中是否有这条数据
                if (mysql_result($sum,0) == 0) {//找不到对应数据（执行insert）
                  $sql = "INSERT INTO ocenter_org_grade_class (id,school_id,name,nj,master_name) VALUES ('". $tableData[$i]["syncId"]."','".$tableData[$i]["schoolId"]."','".$tableData[$i]["name"]."','".$tableData[$i]["nj"]."','".$tableData[$i]["masterName"]."')";
                  mysql_query($sql);
                } else {//找到对应数据(执行update)
                  $sql = "UPDATE ocenter_org_grade_class SET school_id ='".$tableData[$i]["schoolId"]."',name='".$tableData[$i]["name"]."',nj='".$tableData[$i]["nj"]."',master_id='".$tableData[$i]["masterId"]."',master_name='".$tableData[$i]["masterName"]."'"." WHERE id ='".$tableData[$i]["syncId"]."'";
                  mysql_query($sql);
                }
              }
            }

            if ($arr['event'] == "DELETE") {//删除数据
              for ($i=0; $i < $num; $i++) {
                $sql = "DELETE FROM ocenter_org_grade_class WHERE id = '".$tableData[$i]["syncId"]."'";
                mysql_query($sql);
              }
            }
          }


          if ($tableName == 'change_state_org_school') {//对应org_school表

            if ($arr['event'] == "INSERT") {//插入数据
              for ($i=0; $i < $num; $i++) {
                $str = "('". $tableData[$i]["syncId"]."','".$tableData[$i]["parentId"]."','".$tableData[$i]["name"]."','".$tableData[$i]["type"]."'),".$str;
              }
              $str = trim($str,",");
              $sql = "INSERT INTO ocenter_org_school (id,parent_id,name,type) VALUES ".$str;
              // echo $sql;
              mysql_query($sql);
            }

            if ($arr['event'] == "MODIFY") {//修改数据
              for ($i=0; $i < $num; $i++) {
                $sum = mysql_query("SELECT count(*) FROM ocenter_org_school WHERE id = '".$tableData[$i]['syncId']."'");//查看库中是否有这条数据
                if (mysql_result($sum,0) == 0) {//找不到对应数据（执行insert）
                  $sql = "INSERT INTO ocenter_org_school (id,parent_id,name,type) VALUES ('". $tableData[$i]["syncId"]."','".$tableData[$i]["parentId"]."','".$tableData[$i]["name"]."','".$tableData[$i]["type"]."')";
                  mysql_query($sql);
                } else {//找到对应数据(执行update)
                  $sql = "UPDATE ocenter_org_school SET parent_id ='".$tableData[$i]["parentId"]."',name='".$tableData[$i]["name"]."',type='".$tableData[$i]["type"]."'"." WHERE id ='".$tableData[$i]["syncId"]."'";
                  mysql_query($sql);
                }
              }
            }

            if ($arr['event'] == "DELETE") {//删除数据
              for ($i=0; $i < $num; $i++) {
                $sql = "DELETE FROM ocenter_org_school WHERE id = '".$tableData[$i]["syncId"]."'";
                mysql_query($sql);
              }
            }
          }


          if ($tableName == 'change_state_ref_teacher_class') {//对应ref_teacher_class表

            if ($arr['event'] == "INSERT") {//插入数据
              for ($i=0; $i < $num; $i++) {
                $str = "('". $tableData[$i]["syncTeacherId"]."','".$tableData[$i]["type"]."','".$tableData[$i]["syncClassId"]."'),".$str;
              }
              $str = trim($str,",");
              $sql = "INSERT INTO ocenter_ref_teacher_class (teacher_id,type,class_id) VALUES ".$str;
              // echo $sql;
              mysql_query($sql);
            }

            if ($arr['event'] == "MODIFY") {//修改数据
              
            }

            if ($arr['event'] == "DELETE") {//删除数据
              for ($i=0; $i < $num; $i++) {
                $sql = "DELETE FROM ocenter_ref_teacher_class WHERE teacher_id = '".$tableData[$i]["syncTeacherId"]."' AND class_id = '".$tableData[$i]["syncClassId"]."'";
                mysql_query($sql);
              }
            }
          }


          if ($tableName == 'change_state_user_patriarch') {//对应user_patriarch表

            $type = 2;
            if ($arr['event'] == "INSERT") {//插入数据
              for ($i=0; $i < $num; $i++) {
                $str = "('". $tableData[$i]["syncId"]."','".$tableData[$i]["name"]."','".$tableData[$i]["studentId"]."'),".$str;
                $strtwo = "('". $tableData[$i]["name"]."(家)"."','".$tableData[$i]["account"]."','".$type."','".$tableData[$i]["syncId"]."'),".$strtwo;//member表数据
                adduser($tableData[$i]["account"],$cookie);
              }
              $str = trim($str,",");
              $strtwo = trim($strtwo,",");
              $sql = "INSERT INTO ocenter_user_patriarch (id,name,student_id) VALUES ".$str;
              $sqltwo = "INSERT INTO ocenter_member (nickname,account,type,parent_id) VALUES ".$strtwo;//向member表中插入家长信息
              // echo $sql;
              mysql_query($sql);
              mysql_query($sqltwo);
            }

            if ($arr['event'] == "MODIFY") {//修改数据
              for ($i=0; $i < $num; $i++) {
                $sum = mysql_query("SELECT count(*) FROM ocenter_user_patriarch WHERE id = '".$tableData[$i]['syncId']."'");//查看库中是否有这条数据
                if (mysql_result($sum,0) == 0) {//找不到对应数据（执行insert）
                  $sql = "INSERT INTO ocenter_user_patriarch (id,name,student_id) VALUES ('". $tableData[$i]["syncId"]."','".$tableData[$i]["name"]."','".$tableData[$i]["studentId"]."')";
                  $sqltwo = "INSERT INTO ocenter_member (nickname,account,type,parent_id) VALUES ('". $tableData[$i]["name"]."(家)"."','".$tableData[$i]["account"]."','".$type."','".$tableData[$i]["syncId"]."')";
                  mysql_query($sql);
                  mysql_query($sqltwo);
                  adduser($tableData[$i]["account"],$cookie);
                } else {//找到对应数据(执行update)
                  $sql = "UPDATE ocenter_user_patriarch SET name ='".$tableData[$i]["name"]."',student_id='".$tableData[$i]["studentId"]."'"." WHERE id ='".$tableData[$i]["syncId"]."'";
                  $sqltwo = "UPDATE ocenter_member SET nickname ='".$tableData[$i]["name"]."(家)"."',account='".$tableData[$i]["account"]."',type='".$type."'"." WHERE parent_id ='".$tableData[$i]["syncId"]."'";
                  mysql_query($sql); 
                  mysql_query($sqltwo);
                }
              }
            }

            if ($arr['event'] == "DELETE") {//删除数据
              for ($i=0; $i < $num; $i++) {
                $sql = "DELETE FROM ocenter_user_patriarch WHERE id = '".$tableData[$i]["syncId"]."'";
                $sqltwo = "DELETE FROM ocenter_member WHERE parent_id = '".$tableData[$i]["syncId"]."'";
                mysql_query($sql);
                mysql_query($sqltwo);
              }
            }
          }


          if ($tableName == 'change_state_user_student') {//对应user_student表
            $type = 0;
            if ($arr['event'] == "INSERT") {//插入数据
              for ($i=0; $i < $num; $i++) {
                $str = "('". $tableData[$i]["syncId"]."','".$tableData[$i]["schoolId"]."','".$tableData[$i]["classId"]."','".$tableData[$i]["xsxm"]."'),".$str;
                $strtwo = "('". $tableData[$i]["xsxm"]."(学)"."','".$tableData[$i]["account"]."','".$type."','".$tableData[$i]["schoolId"]."','".$tableData[$i]["classId"]."','".$tableData[$i]["syncId"]."'),".$strtwo;//member表数据
                adduser($tableData[$i]["account"],$cookie);
              }
              $str = trim($str,",");
              $strtwo = trim($strtwo,",");
              $sql = "INSERT INTO ocenter_user_student (id,school_id,class_id,xsxm) VALUES ".$str;
              $sqltwo = "INSERT INTO ocenter_member (nickname,account,type,school_id,class_id,student_id) VALUES ".$strtwo;//向member表中插入家长信息
              // echo $sql;
              mysql_query($sql);
              mysql_query($sqltwo);
            }

            if ($arr['event'] == "MODIFY") {//修改数据
              for ($i=0; $i < $num; $i++) {
                $strid = $tableData[$i]['syncId'];
                $sum = mysql_query("SELECT count(*) FROM ocenter_user_student WHERE id = '".$strid."'");//查看库中是否有这条数据
                if (mysql_result($sum,0) == 0) {//找不到对应数据（执行insert）
                  $sql = "INSERT INTO ocenter_user_student (id,school_id,class_id,xsxm) VALUES ('". $tableData[$i]["syncId"]."','".$tableData[$i]["schoolId"]."','".$tableData[$i]["classId"]."','".$tableData[$i]["xsxm"]."')";
                  $sqltwo = "INSERT INTO ocenter_member (nickname,account,type,school_id,class_id,student_id) VALUES ('". $tableData[$i]["xsxm"]."(学)"."','".$tableData[$i]["account"]."','".$type."','".$tableData[$i]["schoolId"]."','".$tableData[$i]["classId"]."','".$tableData[$i]["syncId"]."')";
                  mysql_query($sql);
                  mysql_query($sqltwo);
                  adduser($tableData[$i]["account"],$cookie);
                } else {//找到对应数据(执行update)
                  $sql = "UPDATE ocenter_user_student SET school_id ='".$tableData[$i]["schoolId"]."',xsxm='".$tableData[$i]["xsxm"]."',class_id='".$tableData[$i]["classId"]."'"." WHERE id ='".$tableData[$i]["syncId"]."'";
                  $sqltwo = "UPDATE ocenter_member SET nickname ='".$tableData[$i]["xsxm"]."(学)"."',account='".$tableData[$i]["account"]."',school_id='".$tableData[$i]["schoolId"]."',class_id='".$tableData[$i]["classId"]."'"." WHERE student_id ='".$tableData[$i]["syncId"]."'";
                  mysql_query($sql);
                  mysql_query($sqltwo);
                }
              }
            }
            if ($arr['event'] == "DELETE") {//删除数据
              for ($i=0; $i < $num; $i++) {
                $sql = "DELETE FROM ocenter_user_student WHERE id = '".$tableData[$i]["syncId"]."'";
                $sqltwo = "DELETE FROM ocenter_member WHERE student_id = '".$tableData[$i]["syncId"]."'";
                mysql_query($sql);
                mysql_query($sqltwo);
              }
            }
          }


          if ($tableName == 'change_state_user_teacher') {//对应user_teacher表

            $type = 1;
            if ($arr['event'] == "INSERT") {//插入数据
              for ($i=0; $i < $num; $i++) {
                $str = "('". $tableData[$i]["syncId"]."','".$tableData[$i]["schoolId"]."','".$tableData[$i]["departmentId"]."','".$tableData[$i]["name"]."'),".$str;
                $strtwo = "('". $tableData[$i]["name"]."(师)"."','".$tableData[$i]["account"]."','".$type."','".$tableData[$i]["schoolId"]."','".$tableData[$i]["syncId"]."'),".$strtwo;//member表数据
                adduser($tableData[$i]["account"],$cookie);
              }
              $str = trim($str,",");
              $strtwo = trim($strtwo,",");
              $sql = "INSERT INTO ocenter_user_teacher (id,school_id,department_id,name) VALUES ".$str;
              $sqltwo = "INSERT INTO ocenter_member (nickname,account,type,school_id,teacher_id) VALUES ".$strtwo;//向member表中插入家长信息
              // echo $sql;
              mysql_query($sql);
              mysql_query($sqltwo);
            }
            if ($arr['event'] == "MODIFY") {//修改数据
              for ($i=0; $i < $num; $i++) {
                $sum = mysql_query("SELECT count(*) FROM ocenter_user_teacher WHERE id = '".$tableData[$i]['syncId']."'");//查看库中是否有这条数据
                if (mysql_result($sum,0) == 0) {//找不到对应数据（执行insert）
                  $sql = "INSERT INTO ocenter_user_teacher (id,school_id,department_id,name) VALUES ('". $tableData[$i]["syncId"]."','".$tableData[$i]["schoolId"]."','".$tableData[$i]["departmentId"]."','".$tableData[$i]["name"]."')";
                  $sqltwo = "INSERT INTO ocenter_member (nickname,account,type,school_id,teacher_id) VALUES ('". $tableData[$i]["name"]."(师)"."','".$tableData[$i]["account"]."','".$type."','".$tableData[$i]["schoolId"]."','".$tableData[$i]["syncId"]."')";
                  mysql_query($sql);
                  mysql_query($sqltwo);
                  adduser($tableData[$i]["account"],$cookie);
                } else {//找到对应数据(执行update)
                  $sql = "UPDATE ocenter_user_teacher SET school_id ='".$tableData[$i]["schoolId"]."',department_id='".$tableData[$i]["departmentId"]."',name='".$tableData[$i]["name"]."'"." WHERE id ='".$tableData[$i]["syncId"]."'";
                  $sqltwo = "UPDATE ocenter_member SET nickname ='".$tableData[$i]["name"]."(师)"."',account='".$tableData[$i]["account"]."',school_id='".$tableData[$i]["schoolId"]."'"." WHERE teacher_id ='".$tableData[$i]["syncId"]."'";
                  mysql_query($sql);
                  mysql_query($sqltwo);
                }
              }
            }
            if ($arr['event'] == "DELETE") {//删除数据
              for ($i=0; $i < $num; $i++) {
                $sql = "DELETE FROM ocenter_user_teacher WHERE id = '".$tableData[$i]["syncId"]."'";
                $sqltwo = "DELETE FROM ocenter_member WHERE teacher_id = '".$tableData[$i]["syncId"]."'";
                mysql_query($sql);
                mysql_query($sqltwo);
              }
            }
          }

        }
        // mark the message as received in the queue
        $stomp->ack($msg);
    } else {
        echo "Failed to receive a message";
    }   
}


function adduser($name,$cookie){//向TT服务器插入用户
  $url2 = "http://127.0.0.1/tt/user/add";
  $password = substr(md5($name),-8);//把账户MD5加密后截取后八位作为密码
  $data = array("name" => $name,"password" => $password,"status" => 0,"departId"=>1);
  $content = get_content($url2, $data, $cookie);
}

function login_post($url, $cookie, $post) {
    $curl = curl_init();//初始化curl模块
    curl_setopt($curl, CURLOPT_URL, $url);//登录提交的地址
    curl_setopt($curl, CURLOPT_HEADER, 0);//是否显示头信息
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);//是否自动显示返回的信息
    curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie); //设置Cookie信息保存在指定的文件中
    curl_setopt($curl, CURLOPT_POST, 1);//post方式提交
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));//要提交的信息
    curl_exec($curl);//执行cURL
    curl_close($curl);//关闭cURL资源，并且释放系统资源
} 

function get_content($url, $data, $cookie) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie); //读取cookie
    curl_setopt($ch,CURLOPT_POST,true); //设置为POST请求
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    $rs = curl_exec($ch); //执行cURL抓取页面内容
    curl_close($ch);
    return $rs;
}
@ unlink($cookie);
?>
