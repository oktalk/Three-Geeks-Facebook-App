<?php


//  print_r($_REQUEST);
//  exit();


  $app_id = "494168277279995";
  $canvas_page = "http://threegeeks.debraheightswesleyan.org/";
  $auth_url = "http://www.facebook.com/dialog/oauth?client_id="
    . $app_id . "&redirect_uri=" . urlencode($canvas_page);
    
  $signed_request = $_REQUEST["signed_request"];
  list($encoded_sig, $payload) = explode('.', $signed_request, 2);
  
  $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
  
  if(empty($data["user_id"])){
    echo("<script> top.location.href = '" . $auth_url . "'</script>");
  }else{
//    echo("Welcome User: " . $data["user_id"]);
//    echo("<pre>");
//    print_r($data["oauth_token"]);
//    echo("</pre>");
    
    $base_url = "https://graph.facebook.com/";
    $queryStr = "?access_token=" . $data["oauth_token"];
    
    $frJSON = getStuff($base_url . "me/friends" . $queryStr);
    $friends = json_decode($frJSON, true);

    $wgJSON = getStuff($base_url . "150666328281650/members" . $queryStr);
    $members = json_decode($wgJSON, true);
    
    $member_ids = array();
    foreach($members['data'] as $member) {
    	$member_ids[] = $member['id'];
    }
    
    $geeks = array();
    foreach($friends['data'] as $friend) {
    	if(in_array($friend['id'], $member_ids)) {
        	$geeks[] = $friend;
        }
    }
    
    $percentage = sizeof($geeks) / sizeof($friends);
    
  }


function getStuff($url){
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $response = curl_exec($ch);
  curl_close($ch);
  return $response;
}

?>
<!DOCTYPE html>
<html>
<head>
<style type="text/css">
@import url(http://fonts.googleapis.com/css?family=Advent+Pro);


body {margin:0;padding:0;
font:13px/1.5 sans-serif;color:#666;text-shadow: 1px 0px 1px #FFF;
background:#f4f4f4;
}
#wrapper {width:80%;margin:auto;}
#logo {
  width: 175px;
  display:block;
  margin:20px 0;
}
#page-title {
font-size:40px;color:#CB7243; font-family: 'Advent Pro', serif;
text-shadow: 1px 0px 1px #f01111;    
}
#freinds {margin:0;padding:0;list-style:none}
#freinds li {padding:10px;float:left;background:#EEE;width:100px;
-webkit-box-shadow:inset 0px 0px 5px 0px rgba(255, 255, 255, .5);        
box-shadow: inset 0px 0px 5px 0px rgba(255, 255, 255, .5);
    border:1px solid #DDD;align:center;
}
#freinds img {display:block;margin:0 auto;padding:0;}
.photo {
    border: 1px solid #888;
-webkit-border-radius: 5px;
border-radius: 5px;
-webkit-box-shadow:  0px 0px 5px 0px rgba(0, 0, 0, .5);        
box-shadow:  0px 0px 5px 0px rgba(0, 0, 0, .5);
}
#freinds li p {display:block;}
.name {font-size: 18px;}
</style>


</head><body>
<div id="wrapper">


<img src="http://www.dsmwebgeeks.com/wp-content/themes/dsmweb2012/images/webgeekslogo.png" id="logo">
    
    <div id="geek-level">
        <p>Your friend list is <?php echo number_format($percentage, 2); ?>% geeky.</p>
    </div>
    
    <h1 id="page-title">YourGeekyFriends/</h1>
    
    <ul id="freinds">
<?php
    foreach($geeks as $geek){
?>
        <li>
        <img src="https://graph.facebook.com/<?php echo $geek['id']; ?>/picture" class="photo">
        <div class="copy">

           <p class="name"><?php echo $geek['name']; ?></p>
           
        </div>            
        </li>
<?php
    }
?>
    </ul>
</div>
</body>
</html>