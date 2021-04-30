<!DOCTYPE html>
<html>

<head>
  <style type="text/css">
    #thr_table {
      font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
      width: 600;
      border-collapse: collapse;
    }

    #thr_table td,
    #thr_table th {
      font-size: 1em;
      border: 1px solid #8181F7;
      /*放映表的表格分隔栏*/
      padding: 3px 7px 2px 7px;
    }

    #thr_table th {
      font-size: 1.1em;
      text-align: center;
      padding-top: 5px;
      padding-bottom: 4px;
      background-color: #A9A9F5;
      color: #ffffff;
    }

    #thr_table tr.alt td {
      color: #000000;
      background-color: #EAF2D3;
    }

    .button {
      width: 140px;
      line-height: 38px;
      text-align: center;
      font-weight: bold;
      color: #fff;
      /*返回中的字体颜色*/
      text-shadow: 1px 1px 1px #333;
      border-radius: 5px;
      margin: 0 20px 20px 0;
      position: relative;
      overflow: hidden;
    }

    input[type="text"] {
      background-color: #A9A9F5;
    }

    input[type="submit"] {
      font-family: Trebuchet MS;
      background-color: #A9E2F3;
      width: relative;
      height: 30px;
    }

    body {
      font-family: Georgia;
      font-size: 20px;
      background-color: #FBEFF2;
    }

    div#container {
      width: 1300px;
    }

    div#header {
      background-attachment: scroll;
      height: 50px;
      text-align: center;
    }


    div#menu {
      height: 500px;
      width: 200px;
      float: left;
    }

    div#content {
      background-color: #F0FFFF;
      height: 500px;
      width: 1100px;
      text-align: center;
    }

    div#footer {
      clear: both;
      text-align: center;
    }

    h1 {
      margin-bottom: 0;
      color: #84d494;
    }

    h2 {
      margin-bottom: 0;
      font-size: 14px;
    }

    ul {
      margin: 0;
      float: left;
    }

    li {
      list-style: none;
    }
  </style>
  <h1>
    <center>电影放映管理系统</center>
  </h1>
</head>

<body>
  <div id="container">
  </div>


  <br>
  <center>---------------<选择你想要进行的操作>---------------</center><br>
  <?php
  /*
*/
  require "config.php";
  //显示操作
  function startform()
  {
    print"<center>
          <input type=submit name=del value=删除电影>
          <input type=submit name=save value=添加电影>
          <input type=submit name=add value=添加放映信息>
          <input type=submit name=mod value=修改价格>             
			    <input type=submit name=list value=电影放映表>
          </center>";
  }


  //含有事务应用的删除操作
  //删除电影的信息，如果该电影有对应的放映信息，则也随之删除
  function delform() //删除信息
  {
    require "config.php";
    $sql = "select* from $MVE;";
    $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("无法连接");
    $db = mysqli_select_db($connect, $DB_NAME) or die("无法连接到theaters");
    $query = mysqli_query($connect, $sql) or die("执行失败！！！");
    if ($query) {
      print "<br><br><select name=selmovie>";
      while ($list = mysqli_fetch_array($query)) {//从结果集中取得一行作为数字数组或关联数组
        print "<option value=$list[movid]>$list[movname]";//设置下拉的目录
      }
      print "</select><input type=submit name=dodel value=选定你想删除的电影名><br><br>";//触发删除
    }
  }

  function dodel($selname) //删除电影信息
  {
    //print $selname;
    require "config.php"; //连接数据库的信息
    $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("无法连接");
    $db = mysqli_select_db($connect, $DB_NAME) or die("无法连接到theaters数据库");
    mysqli_query('START TRANSACTION');//开始事务

    $sql = "select * from $DPL where movid='$selname'";
    $query = mysqli_query($connect, $sql) or die("在display中查找失败");
    $list = mysqli_fetch_array($query);
    //PRINT "$list[playid]";
    if ($list[playid]) //删除的电影存在放映信息表display中，再进行判断是否进行删除
    {
      print "<p id=\"thr_table\"><th><center>以下就是该部电影所有的放映信息：</center></th></p>";
      //显示放映列表, 可以提醒用户该电影有放映信息！！
      print "<table  id=\"thr_table\">";
      print "<tr class=\"alt\">
             <th>电影名</th><th>影院</th><th>价格</th><th>类型</th><th>放映时间</th><th>介绍</th></tr>";

      $sql = "select * from playinfo where movid='$selname'";//第7小问中建立的视图playinfo
      $query = mysqli_query($connect, $sql) or die("在display中执行查找失败");

      while ($listlist = mysqli_fetch_array($query)) {//进行展示
      print " <tr ><td>$listlist[movname]</td>
                   <td>$listlist[thrname]</td>
                   <td>$listlist[thrprice]</td>
                   <td>$listlist[pricetype]</td>
                   <td>$listlist[playtime]</td>
                   <td>$listlist[intro]</td>
             </tr> ";
      }
      print "</table>";//表格显示完毕
      //显示出该电影，用submit的方式提交，若用户点击则跳转到casadeddel函数进行级联删除！
      print "<br><select name=selmovie>";
      print "<option value=$selname>$list[movname]";
      print "</select><input type=submit name=casadedel value=确认删除><br>";
      //撤销删除，跳转到事物回滚roll_back函数
      print "<input type=submit name=roll_back value=我再犹豫一会><br><br>";
    } else //放映信息里面没有要删除的电影，可以直接删除
    {
      $sql = "delete from $MVE where  movid='$selname';"; //状态符合要求，可以进行删除
      $query1 = mysqli_query($connect, $sql) or die("Executing failed 2");
      if (!$query1) {
        mysqli_query('ROLLBACK');
        print "deleting error-->rollback 2";
      } else {
        mysqli_query('COMMIT');
        print "<br>成功删除 $selname !</br>";
      }
    }
  }
  function roll_back() //进行事务回滚
  {
    require "config.php";
    $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("连接失败");
    $db = mysqli_select_db($connect, $DB_NAME) or die("连接数据库失败");
    mysqli_query($connect, 'ROLLBACK');//调用rollback
    print "<br><p id=\"thr_table\"><th>!!!!已放弃删除!!!!</th></p>";
  }
  function casadedel($selname) //进行级联删除
  { //print "casadele!!!!!!!!!!! $selname !!!!!!!!!!!!!";
    require "config.php"; //连接数据库的信息
    $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("连接失败");
    $db = mysqli_select_db($connect, $DB_NAME) or die("连接到theaters数据库失败");
    //删除与该电影有关的订单消息
    $sqld = "delete from $ORD where playid=(select playid from $DPL where movid='$selname' )";
    //print $sql;删除与该电影有关的放映消息
    $queryd = mysqli_query($connect, $sqld) or die("Executing failed 3");
    $sqld2 = "delete from $DPL where movid='$selname'";
    //删除电影信息
    $queryd = mysqli_query($connect, $sqld2) or die("Executing failed 4");
    $sql = "delete from $MVE where  movid='$selname'"; //状态符合要求，可以进行删除
    $query1 = mysqli_query($connect, $sql) or die("Executing failed 2");
    if (!$query1 || !$queryd) {
      mysqli_query('ROLLBACK');
      print "deleting error-->rollback 2";
    } else {
      mysqli_query('COMMIT');
      print "<br><p id=\"thr_table\"><th>成功删除 $selname ！</th></p><br>";
    }
  }



  ///////触发器，添加电影，触发操作，将电影介绍intro改成 “new movie!Welcome to choose!”///////
  function saveform()
  {/* 
      CREATE TRIGGER `addtrg` BEFORE INSERT ON `movies`
      FOR EACH ROW 
      BEGIN IF new.movid NOT IN ( SELECT movid FROM display ) 
      THEN SET new.intro = 'new movie!Welcome to choose!'; END IF ; END
      $tempid=$tempid+1;*/
    //触发器在mysql数据库里面添加，这里因为sql语句的转义符问题不能被myphpadmin正确执行，不可以添加trigger。
    require "config.php";
    $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("无法连接");
    $db = mysqli_select_db($connect, $DB_NAME) or die("无法选择数据库");
    /*print "<p id=\"thr_table\"><tr><th>***Please use \OR' before inputing ' for SQL grammer***</th></tr></p>";*/
    print "
       <center>   
       <br><table id=\"thr_table\">
       <tr><th>电影名称<td><input type=text name=mov_name>
       <tr><th>发行年份<td><input type=text name=mov_year>
       <tr><th>导演名<td><input type=text name=mov_dir>
       <tr><th>相关介绍<td><input type=text name=mov_intro>
       <tr><td colspan=2><center><input type=submit name=dosave value=保存电影信息></center>
       </table><br>";
  }
  //保存至数据库**************************************************************
  function saveend($mov_name, $mov_year, $mov_dir, $mov_intro)
  {
    if ($mov_name == "" || $mov_year == "" || $mov_dir == "") //||$remark=="")
    {
      print "<p id=\"thr_table\"><tr><th>不能为空!!!</th></tr></p>";
      echo "<certer><br><form><input type=button class=button value=\"返回\"     onclick=\"history.back();\"></form>";
      echo "<br></center>";
      exit;
    }
    require "config.php";

    $sql = "select * from $MVE where $MVE.movid>=all(select movid from $MVE);";

    $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("无法连接");
    $db = mysqli_select_db($connect, $DB_NAME) or die("无法选择数据库");
    $query = mysqli_query($connect, $sql) or die("无法执行SQL语法");
    $list = mysqli_fetch_array($query);
    $id = $list[movid] + 1;//id进行加一

    $sql = "insert into $MVE values('$mov_name','$id','$mov_year','$mov_dir','$mov_intro');";
    // $connect=mysqli_connect($DB_SERVER,$DB_USER,$DB_PASS)or die("无法连接");
    // $db=mysqli_select_db(  $connect,$DB_NAME)or die("无法选择数据库");
    //print $sql;
    $query = mysqli_query($connect, $sql) or die("无法保存信息");

    if ($query) {
      print "<p id=\"thr_table\"><th>---成功保存电影信息---</th></p>";
    } else {
      print "!!!保存错误!!!";
    }
  }


  ////////////////触发器下的添加操作//////////////
  //添加电影放映信息，涉及到触发器，若添加的电影不在电影表中，则自动添加该电影到电影表当中
  //若添加的放映信息当中电影院不存在电影院的表中，报错。
  function addplay()
  {
    require "config.php";
    print "
       <center>   
       <br><br><table id=\"thr_table\">
       <tr><th>电影ID<td><input type=text name=mov_id>
       <tr><th>影院ID<td><input type=text name=thr_id>
       <tr><th>价格ID<td><input type=text name=price_id>
       <tr><th>放映时间<td><input type=text name=play_time>
       <tr><td colspan=2><center><input id=\"thr_but\" type=submit name=doadd value=保存电影放映信息></center>
       </table><br>                       
       ";
  }

  function addplayfin($mov_id, $mov_name, $thr_id, $price_id, $play_time)//添加至数据库
  {
    if (($mov_id == "" && $mov_name == "") || $thr_id == "" || $price_id == "" || $play_time == "") {
      print "<p id=\"thr_table\"><th><center>不能为空!!!</center></th></p>";
      echo "<certer><br><form>
            <input type=button class=button value=\"返回\"onclick=\"history.back();\"></form>";
      echo "<br></center>";
      exit;
    }

    require "config.php";
    /////////添加放映信息的触发器/////////////  尚有一点问题
    /*  $sql="
             CREATE TRIGGER `play_check` BEFORE INSERT ON `display`
             FOR EACH ROW 
             begin 
                     if new.movid not in(select movid from movies) 
                       then insert into movies(movid,movname) values(new.movid,new.movname);
                    end if; 
             end
             ";*/
    $sql = "select * from $DPL where $DPL.playid>=all(select playid from $DPL)";
    $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("连接错误 1");
    $db = mysqli_select_db($connect, $DB_NAME) or die("选择数据库失败 1");
    //print $sql;
    $query = mysqli_query($connect, $sql) or die("sql语句出错???");
    $list = mysqli_fetch_array($query);
    $id = $list[playid] + 1;//id进行加一
    $sqltest = "select * from $THR where thrid=$thr_id"; //判断电影院存在否？
    $querytest = mysqli_query($connect, $sqltest) or die("电影院不存在");
    $listtest =  mysqli_fetch_array($querytest);
    $thridtest = $listtest[thrname];
    $sqltest2 = "select * from $PRI where priceid=$price_id"; //判断价格存在否？！！！！！！！！！！！出错priceid不能等于0
    $querytest2 = mysqli_query($connect, $sqltest2) or die("价格不存在2");
    $listtest2 =  mysqli_fetch_array($querytest2);
    $thridtest2 = $listtest2[priceid];
    $flag = 0; //判断电影的信息写得对不对,初始为0,表示不对
    if ($thridtest && $thridtest2) {
      if ($mov_id) //写了电影id
      {
        if ($mov_name == "") //没写电影名称
        {
          $sql = "select * from $MVE where movid='$mov_id'";
          $query = mysqli_query($connect, $sql) or die("sql fail1");
          $movid =  mysqli_fetch_array($query);
          if ($movid[movid]) //所填电影id在movies表中，符合条件
          {
            $flag = 1;
            $mov_name = $movid[movname];
          }
        } else //写了电影名称
        {
          $sql = "select * from $MVE where movid=$mov_id";
          $query = mysqli_query($connect, $sql) or die("sql fail2");
          $movid =  mysqli_fetch_array($query);
          if (!$movid[id]) //电影id不在movies表中
          {
            $sql = "select * from $MVE where movname='$mov_name'";
            $query = mysqli_query($connect, $sql) or die("sql fail3");
            $movname =  mysqli_fetch_array($query);
            if (!$movname)
            //电影名称和电影id都不在movies表中，说明是新的电影，允许插入，会执行另一个触发器自动添加新电影
            {
              $flag = 1;
            } else {
              $flag = 0;
            } //若电影名称不在，而电影id存在，说明输入有误
          }
          if ($movie[movname] == $mov_name) //电影id和电影名称相符合
          {
            $flag = 1;
          }
        }
        if ($mov_id < 1000 || $mov_id > 9999) $flag = 0; //电影id不符合要求，须在1000到9999之间！
      } else //没输入电影id
      {
        $sql = "select * from $MVE where movname=$mov_name";
        $movid =  mysqli_fetch_array($sql);
        if ($movid[movid]) //所填电影id在movies表中，符合条件
        {
          $flag = 1;
          $mov_id = $movid[movid];
        }
      }

      if ($flag) {//所填的信息都正确
        $sqlinsert = "insert into $DPL values('$mov_id','$thr_id','$price_id','$play_time','$id','$mov_name');";
        //print "$sqlinsert";
        $query = mysqli_query($connect, $sqlinsert) or die("不能保存信息");
        if ($query) {
          print "<p id=\"thr_table\"><th>---成功保存放映信息---</th></p>";
        } else {
          print "!!!保存错误!!!";
        }
      } else //电影信息不符合要求，报错
      {
        print "<p id=\"thr_table\"><th>错误，请检查电影信息!</th></p>";
        echo "<br><certer>
        <input type=button class=button value=\"返回\"     onclick=\"history.back();\">";
        echo "</center><br>";
      }
    } else //电影院不存在或价格不存在！！！
    {
      print "<p id=\"thr_table\"><th>错误，请检查电影院或者价格信息!</th></p>";
      echo "<certer><br><form>
      <input type=button class=button value=\"返回\"     onclick=\"history.back();\"></form>";
      echo "<br></center>";
    }
  }



  ////////// 存储过程的更新操作////////////
  //修改电影票的价格，然后存储过程将会把预定表orders中的与该电影票相关的订单的价格全部更新为新价格
  function modform()
  {
    require "config.php";
    $sql = "select* from $PRI;"; //mysqli_query("set names gb2312"); 
    $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("连接失败");
    $db = mysqli_select_db($connect, $DB_NAME) or die("连接theaters数据库失败");
    $query = mysqli_query($connect, $sql) or die("sql语句执行失败");
    if ($query) {
      print "<br><center><select id=\"thr_but\" name=selname>";
      while ($list = mysqli_fetch_array($query)) {
        print "<option value=$list[priceid]>$list[pricetype]"; //the selected element's value is its "id"
      }
      print "</select><input type=submit name=showmod value=确认修改>";
    }
  }
  function showmod($selname) //执行修改内容
  {
    require "config.php";
    $sql = "select* from $PRI where priceid='$selname';"; //mysqli_query("set names gb2312"); 
    $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("连接失败");
    $db = mysqli_select_db($connect, $DB_NAME) or die("选择失败");
    //print $sql;
    $query = mysqli_query($connect, $sql) or die("执行失败");
    if ($query) {
      while ($list = mysqli_fetch_array($query)) {
        $id = $list[priceid];
        $price = $list[thrprice];
        $type = $list[pricetype];
      }
      print "<tr><tr><table id=\"thr_table\">
				 <br><br><tr ><center><th colspan=2>价格修改</center></tr>
         <tr><th >priceid
         <td><input type=hidden name=price_id value=$id></tr>
         <tr><th >price
         <td><input type=text name=price_ value=$price></tr>
				 <tr><th >type
         <td><input type=text name=price_type value=$type></tr>
				 <tr><td colspan=2><center><input type=submit name=domod value=提交修改></center> </table>
				 ";
    }
  }
  function domod($priceid, $price, $type) //完成修改价格和存储过程更新操作
  {
    require "config.php";
    $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("连接失败");
    $db = mysqli_select_db($connect, $DB_NAME) or die("选择失败");
    /* $sql="DROP PROCEDURE IF EXISTS `mod_price`";
      $query=mysqli_query( $connect,$sql)or die("EXE FAIL_drop_proc");
      $sql=" 
           CREATE PROCEDURE mod_price(IN id_in INT(11),IN pri_in FLOAT)
           BEGIN 
               UPDATE orders SET totalprice=pri_in*amount
                  WHERE playid in
                  (
                  SELECT playid FROM display dpl
                      WHERE dpl.priceid=id_in
                  );
           END;
           ";
           if(mysqli_query($sql)) {
           print "<p id=\"thr_table\"><center><th>***PROCEDURE OK***</th></center></p>";
       }
       else {print "<br>procedure1 fail!<br>";};
       */
    //存储过程 
    if ($price < 0 || $price > 200) //价格设置不合理
    {
      print "<p id=\"thr_table\"><center><th>!!!请重新检查一下你的价格!!!</th></center></p>";
      echo "<certer><br><form>
              <input type=button class=button value=\"返回\"     onclick=\"history.back();\"></form>";
    } else {
      $sql = "call mod_price($priceid,$price)";//调用mod_price存储过程
      $query = mysqli_query($connect, $sql) or die("过程失败");
      if (!$query) {
        print "调用存储过程失败！";
      } else print "<p id=\"thr_table\"><th>！！！成功调用存储过程！！！</th></p>";
      $sql = "update $PRI set thrprice='$price',pricetype='$type' where priceid='$priceid';";
      $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("连接失败2");
      $db = mysqli_select_db($connect, $DB_NAME) or die("选择失败2");
      $query = mysqli_query($connect, $sql) or die("过程失败2");
      if (!$query) {
        print "修改失败";
      } else {
        print "<p id=\"thr_table\"><th>！！！价格修改成功！！！</th></p>";
      }
    }
  }



  /////////创建视图//////////
  //通过选择电影名称，创建视图，筛选出电影放映信息并显示出来，按键：电影放映表
  function listform()
  {
    require "config.php";
    $sql = "select* from $MVE;";
    $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("连接失败");
    $db = mysqli_select_db($connect, $DB_NAME) or die("失败");
    $query = mysqli_query($connect, $sql) or die("查询失败");
    if ($query) {
      print "<br><table id=\"thr_table\"><tr>可选的电影</tr>";
      print "<br><br><select name=selmovie>";
      while ($list = mysqli_fetch_array($query)) {
        print "<option value=$list[movid]>$list[movname]";
      }
      print "</select><input type=submit name=showmovie value=选择一部你想看的电影><br><br>";
      print "</table>";
    }
  }

  function playview($selmovie)
  {
    require "config.php";
    //若已存在，则drop该视图
    $connect = mysqli_connect($DB_SERVER, $DB_USER, $DB_PASS) or die("连接失败");
    $db = mysqli_select_db($connect, $DB_NAME) or die("选择theaters数据库失败");
    /*$sqli="DROP VIEW IF EXISTS playinfo";$queryi=mysqli_query($sqli,$connect)or die("exe fail0");
       //建立放映信息视图
       $sql="
         CREATE VIEW playinfo
           AS
           select mov.movname,mov.movid,thrname,thrprice,pricetype,playtime,intro 
             from movies mov,display dpl,price pri,theater thr 
             where mov.movid=dpl.movid and thr.thrid=dpl.thrid and pri.priceid=dpl.priceid 
                   "  ;
       $query=mysqli_query( $connect,$sql)or die("exe fail");
       */
    //显示视图
    $sqlv = "select * from playinfo where movid=$selmovie";
    $queryv = mysqli_query($connect, $sqlv) or die("exe failv");
    print "<br><center> 以下就是此部电影的放映信息表：</center>";
    print "<br><table  id=\"thr_table\">";
    print "<tr class=\"alt\">
       <th>电影名</th><th>影院</th><th>价格</th><th>类型</th><th>放映时间</th><th>介绍</th></tr>";
    while ($list = mysqli_fetch_array($queryv)) {//列表依次列举出来
      print " <tr><td>$list[movname]</td>
                  <td>$list[thrname]</td>
                  <td>$list[thrprice]</td>
                  <td>$list[pricetype]</td>
                  <td>$list[playtime]</td>
                  <td>$list[intro]</td></tr>";
    }
    print "</table>";//列举完毕
    echo "<certer><br><form>
          <input type=button class=button value=\"返回\"     onclick=\"history.back();\"></form>";
    echo "<br></center>";
  }





  print "<form method=post action=main.php> <center>";//通过post方法提交表单内容到main.php这个页面
  //(int)$tempid='11100';

  startform();

  $proc = $_POST["procedure"];
  $add = $_POST["add"];
  $doadd = $_POST["doadd"];
  $save = $_POST["save"];
  $dosave = $_POST["dosave"];
  $del = $_POST["del"];
  $dodel = $_POST["dodel"];
  $casadedel = $_POST["casadedel"];
  $roll_back = $_POST["roll_back"];
  //$del_specified=$_POST["del_specified"];
  $mod = $_POST["mod"];
  $showmod = $_POST["showmod"];
  $domod = $_POST["domod"];


  //$srch=$_POST["srch"];
  //$dosrch=$_POST["dosrch"];
  $list = $_POST["list"];
  $showmovie = $_POST["showmovie"];
  if ($save) {
    saveform();//添加电影
  } 
  else if ($dosave) {
    $mov_name = $_POST["mov_name"];
    $mov_year = $_POST["mov_year"];
    $mov_dir = $_POST["mov_dir"];
    $mov_intro = $_POST["mov_intro"];

    saveend($mov_name, $mov_year, $mov_dir, $mov_intro);//保存至数据库
    //saveend($cus_name,$room_type,$room_cnt,$room_price,$room_date,$remark);
  } 
  else if ($add) {
    addplay();//添加放映信息
  } 
  else if ($doadd) {
    $mov_id = $_POST["mov_id"];
    $mov_name = $_POST["mov_name"];
    $thr_id = $_POST["thr_id"];
    $price_id = $_POST["price_id"];
    $play_time = $_POST["play_time"];

    addplayfin($mov_id, $mov_name, $thr_id, $price_id, $play_time);//保存至数据库
  } 
  else if ($del) {
    delform();//删除电影信息
  } 
  else if ($dodel || $casadedel) {
    $selname = $_POST["selmovie"];
    if ($dodel) { //print "$selname";
      dodel($selname);//删除数据库中的信息
    } 
    else { //print "$selname";
      casadedel($selname);//进行级联删除
    }
  } 
  else if ($roll_back) {
    roll_back();//进行事务回滚，放弃删除
  }
  //else if(){casadedel($selname);}
  //else if($del_specified) {$cus_name=$_POST["cus_name"]; $room_date=$_POST["room_date"];del_specified($cus_name,$room_date);}
  else if ($mod) {
    modform();//修改电影票价格
  } 
  else if ($showmod) {
    $selname = $_POST["selname"];
    showmod($selname);//正式执行修改内容
  } 
  else if ($domod) { //$id=$_POST["id"];
    $price = $_POST["price_"];
    $priceid = $_POST["price_id"];
    $type = $_POST["price_type"];
    domod($priceid, $price, $type);//完成修改价格和存储过程更新操作
  } 
  else if ($list) {
    listform();//创建视图，电影放映表
  } 
  else if ($showmovie) {
    $selmovie = $_POST["selmovie"];
    playview($selmovie);//依次列举
  }
  print "</center>
		 </form>";

$sql="
CREATE VIEW playinfo
 AS select mov.movname,mov.movid,thrname,thrprice,pricetype,playtime,intro 
   from movies mov,display dpl,price pri,theater thr 
      where mov.movid=dpl.movid and thr.thrid=dpl.thrid and pri.priceid=dpl.priceid ";
  ?>
</body>
</html>