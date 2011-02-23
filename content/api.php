<?php
header('Content-type: application/json; charset=UTF-8');
include('connect.php');
$result=mysql_query("SELECT url,fn,content,DATE_FORMAT(pubdate,'%a, %d %b %Y %T %Z') AS pubdate FROM $dbtable WHERE url='$url'");
while($row=@mysql_fetch_array($result,MYSQL_ASSOC)){
    $row[]=array('row'=>array_map('htmlspecialchars',$row));
    # HTTP header caching
    function cache($gmtime){
        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])||isset($_SERVER['HTTP_IF_NONE_MATCH'])){
            if($_SERVER['HTTP_IF_MODIFIED_SINCE']==$gmtime){
                header('HTTP/1.1 304 Not Modified');
                exit();
            }
        }
        header("Last-Modified: $gmtime");
        header('Cache-Control: public');
    }
    cache($row['pubdate']); 
    $urlid=strip_tags($row['url']);
    $fn=strip_tags($row['fn']);
    $array=array('url'=>$urlid,'fn'=>$fn,'content'=>"<h1>$fn</h1>\r\n<p>{$row['content']}</p>\r\n");
    $json=str_replace('\\/','/',json_encode($array));
    echo(isset($_GET['callback']) ? $_GET['callback'].'('.$json.')' : $json);
}
mysql_close($con);

# Return 404 error, if url does not exist
$validate=new validate($url);
if($url!==$urlid){
    $validate->status('404');
    echo (isset($_GET['callback']) ? $_GET['callback'].'({})' : '{}');
}
?>