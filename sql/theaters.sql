

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";/*设置自增列从0开始*/
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `theaters`
--
CREATE DATABASE IF NOT EXISTS `theaters` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `theaters`;

DELIMITER $$
--
-- 存储过程
--
DROP PROCEDURE IF EXISTS `mod_price`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `mod_price`(IN id_in INT(11),IN pri_in FLOAT)
BEGIN 
UPDATE orders SET totalprice=pri_in*amount
  WHERE playid in
  (
  SELECT playid FROM display dpl
      WHERE dpl.priceid=id_in
  );
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `customer`
--

DROP TABLE IF EXISTS `customer`;
CREATE TABLE IF NOT EXISTS `customer` (
  `cusid` int(11) NOT NULL DEFAULT '0',
  `cusname` varchar(20) NOT NULL,
  `levelid` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cusid`),
  KEY `FK_tb_level` (`levelid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `customer`
--

INSERT INTO `customer` (`cusid`, `cusname`, `levelid`) VALUES
(1000, 'Mary', 0),
(1001, 'Tom', 0),
(1002, 'Jerry', 0),
(1003, 'Harry', 0),
(1004, 'Edward', 0),
(1005, 'Jack', 0);

-- --------------------------------------------------------

--
-- 表的结构 `display`
--

DROP TABLE IF EXISTS `display`;
CREATE TABLE IF NOT EXISTS `display` (
  `movid` int(11) NOT NULL DEFAULT '0',
  `thrid` int(11) NOT NULL DEFAULT '0',
  `priceid` int(11) NOT NULL DEFAULT '0',
  `playtime` datetime NOT NULL DEFAULT '2020-01-01 18:00:00',
  `playid` int(11) NOT NULL DEFAULT '0',
  `movname` varchar(20) NOT NULL,
  PRIMARY KEY (`playid`),
  KEY `fk_mybbs_author` (`movid`),
  KEY `fk_mybbs_theater` (`thrid`),
  KEY `fk_mybbs_price` (`priceid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `display`
--

INSERT INTO `display` (`movid`, `thrid`, `priceid`, `playtime`, `playid`, `movname`) VALUES
(1000, 0, 0, '2020-05-01 18:00:00', 10000, 'Finding Mr.Right'),
(1000, 0, 1, '2020-05-01 21:00:00', 10001, 'Finding Mr.Right'),
(1000, 0, 2, '2020-05-01 10:00:00', 10002, 'Finding Mr.Right'),
(1000, 1, 0, '2020-05-01 18:00:00', 20000, 'Finding Mr.Right'),
(1009, 2, 2, '2020-05-02 18:00:00', 20002, 'Thrill'),
(1009, 2, 1, '2020-05-02 15:00:00', 20003, 'Finishing SQL'),
(1009, 1, 1, '2020-05-02 15:00:00', 20005, 'Finishing SQL'),
(1010, 1, 1, '2020-05-03 18:00:00', 20006, 'happy ending'),
(1088, 1, 1, '2020-05-03 21:00:00', 20007, 'abcd'),
(1010, 2, 2, '2020-05-03 23:00:00', 20008, 'New Year');

--
-- 触发器 `display`
--
DROP TRIGGER IF EXISTS `play_check`;
DELIMITER //
CREATE TRIGGER `play_check` BEFORE INSERT ON `display`
 FOR EACH ROW begin 
 if new.movid not in(select movid from movies) 
 then insert into movies(movid,movname) values(new.movid,new.movname);
 end if;
 end

                          
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `level`
--

DROP TABLE IF EXISTS `level`;
CREATE TABLE IF NOT EXISTS `level` (
  `levelid` int(1) NOT NULL DEFAULT '0',
  `discount` float NOT NULL DEFAULT '1',
  PRIMARY KEY (`levelid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `level`
--

INSERT INTO `level` (`levelid`, `discount`) VALUES
(0, 1),
(2, 0.9),
(3, 0.8),
(4, 0.6),
(5, 0.5);

-- --------------------------------------------------------

--
-- 表的结构 `movies`
--

DROP TABLE IF EXISTS `movies`;
CREATE TABLE IF NOT EXISTS `movies` (
  `movname` varchar(20) NOT NULL,
  `movid` int(11) NOT NULL,
  `movyear` int(20) NOT NULL DEFAULT '2020',
  `direct` varchar(20) DEFAULT 'Unknown',
  `intro` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`movid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `movies`
--

INSERT INTO `movies` (`movname`, `movid`, `movyear`, `direct`, `intro`) VALUES
('Finding Mr.Right', 1000, 2020, 'Liu lang', 'Very Moving and surprising!'),
('Too Young', 1007, 2020, 'Zhao Wei', 'It''s about youth'),
('Thrill', 1009, 2020, 'Unknown', 'Thrilling and horoble'),
('Merry Christmas', 1010, 2020, 'Jones', 'Happy ending'),
('Romance', 1011, 2020, 'Jane Lius', 'new movie!!!'),
('abcd', 1088, 2020, 'Unknown', 'new movie!!!');

--
-- 触发器 `movies`
--
DROP TRIGGER IF EXISTS `addtrg`;
DELIMITER //
CREATE TRIGGER `addtrg` BEFORE INSERT ON `movies`
 FOR EACH ROW BEGIN IF new.movid NOT IN ( SELECT movid FROM display ) THEN SET new.intro = 'new movie!Welcome to choose!'; 
 END IF ; 
 END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `playid` int(11) NOT NULL DEFAULT '0',
  `cusid` int(11) NOT NULL DEFAULT '0',
  `amount` int(1) NOT NULL DEFAULT '1',
  `totalprice` int(11) NOT NULL,
  `orderid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`orderid`),
  KEY `FK_tb_orders` (`playid`),
  KEY `FK_tb_cus` (`cusid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 替换视图以便查看 `playinfo`
--
DROP VIEW IF EXISTS `playinfo`;
CREATE TABLE IF NOT EXISTS `playinfo` (
`movname` varchar(20)
,`movid` int(11)
,`thrname` varchar(20)
,`thrprice` float
,`pricetype` varchar(10)
,`playtime` datetime
,`intro` varchar(50)
);
-- --------------------------------------------------------

--
-- 表的结构 `price`
--

DROP TABLE IF EXISTS `price`;
CREATE TABLE IF NOT EXISTS `price` (
  `thrprice` float NOT NULL DEFAULT '80',
  `priceid` int(11) NOT NULL DEFAULT '0',
  `pricetype` varchar(10) NOT NULL DEFAULT '2D',
  PRIMARY KEY (`priceid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `price`
--

INSERT INTO `price` (`thrprice`, `priceid`, `pricetype`) VALUES
(90, 0, '2D'),
(110, 1, 'IMAX'),
(150, 2, '3D');

-- --------------------------------------------------------

--
-- 表的结构 `theater`
--

DROP TABLE IF EXISTS `theater`;
CREATE TABLE IF NOT EXISTS `theater` (
  `thrname` varchar(20) NOT NULL DEFAULT 'Sunny Theater',
  `thraddr` varchar(20) NOT NULL DEFAULT 'Green Street 1th',
  `thrid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`thrid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `theater`
--

INSERT INTO `theater` (`thrname`, `thraddr`, `thrid`) VALUES
('Sunny Theater', 'Green Street 1th', 0),
('Happy Theater', 'Sheerly Road 15th', 1),
('Crazy Theater', 'May Road 188th', 2);

-- --------------------------------------------------------

--
-- 视图结构 `playinfo`
--
DROP TABLE IF EXISTS `playinfo`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `playinfo` 
  AS select `mov`.`movname` AS `movname`,`mov`.`movid` AS `movid`,`thr`.`thrname` AS `thrname`,`pri`.`thrprice` AS `thrprice`,`pri`.`pricetype` AS `pricetype`,`dpl`.`playtime` AS `playtime`,`mov`.`intro` AS `intro` 
    from (((`movies` `mov` join `display` `dpl`) join `price` `pri`) join `theater` `thr`) 
      where ((`mov`.`movid` = `dpl`.`movid`) and (`thr`.`thrid` = `dpl`.`thrid`) and (`pri`.`priceid` = `dpl`.`priceid`));

--
-- 限制导出的表
--

--
-- 限制表 `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `FK_tb_level` FOREIGN KEY (`levelid`) REFERENCES `level` (`levelid`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- 限制表 `display`
--
ALTER TABLE `display`
  ADD CONSTRAINT `fk_mybbs_author` FOREIGN KEY (`movid`) REFERENCES `movies` (`movid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_mybbs_price` FOREIGN KEY (`priceid`) REFERENCES `price` (`priceid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_mybbs_theater` FOREIGN KEY (`thrid`) REFERENCES `theater` (`thrid`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- 限制表 `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `FK_tb_cus` FOREIGN KEY (`cusid`) REFERENCES `customer` (`cusid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_tb_orders` FOREIGN KEY (`playid`) REFERENCES `display` (`playid`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;







/*  

// $Customer='customer';
// $Admin='admin';

$sql  ="insert into $THR values('Happy Theater','Green road no.10 ','Charles');";
$connect=mysql_connect($DB_SERVER,$DB_USER,$DB_PASS) or die("Connecting failed");
$db=mysql_select_db($DB_NAME,$connect) or die("Selecting failed");
$query   =mysql_query($sql,$connect) ;//or die("1 Executing failed");
    
    $sql  ="insert into $THR values('Sunshine Theater','Ivy street no.1','Sheerly');";
    $query   =mysql_query($sql,$connect) or die("2 Executing failed");
    
    $sql  ="insert into $DEP values('101','service','1','Happy Theater');";
    $query   =mysql_query($sql,$connect) or die("3 Executing failed");
    $sql  ="insert into $DEP values('102','finance','0','Happy Theater');";
    $query   =mysql_query($sql,$connect) or die("4 Executing failed");
    $sql  ="insert into $DEP values('202','finance','1','Sunshine Theater');";
    $query   =mysql_query($sql,$connect) or die("5 Executing failed");
    
    $sql  ="insert into $EMP values('10001','Happy Theater','Mary','101','3000','0');";
    $query   =mysql_query($sql,$connect) or die("6 Executing failed");
    $sql  ="insert into $EMP values('20001','Sunshine Theater','Jane','202'，'4000','1');";
    $query   =mysql_query($sql,$connect) or die("7 Executing failed");


mysql_close($con);

*/