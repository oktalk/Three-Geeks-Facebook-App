<?php


//  print_r($_REQUEST);
//  exit();


  $app_id = "###";
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
    $frs = json_decode($frJSON, true);
    $friends = array();
    foreach($frs['data'] as $fr){
      $friends[] = $fr['id'];
    }
    

    // Read and parse the JSON file containing the geeky groups we want to check
    $grpJSON = file_get_contents("groups.json");
    $groups = json_decode($grpJSON, true);
    $geeks = array();
    
    // run through the groups and check the membership against user's friend list
    foreach($groups as $group){
      if(!gtg($group["id"], "/^\\d+$/")){ // if I don't have a valid id
        continue;
      }

      
      // Pull and parse the list of members for the list
//      $grpInfo = getStuff($base_url . $group['id'] . $queryStr);
//      $group = json_decode($grpInfo, true);
      
//      $wgJSON = getStuff($base_url . $group['id'] . "/" . (isset($group['likes']) ? "likes" : "members") . $queryStr);
      $wgJSON = getStuff($base_url . $group['id'] . "/members" . $queryStr);
      $members = json_decode($wgJSON, true);
      
//      echo "<pre>";
//      var_dump($members);
//      echo "</pre>";
      
      // loop through the members and see if any are on user's friend list
      foreach($members['data'] as $member){
        if(in_array($member['id'], $friends)){ // if the member is in the friends list
          $memberFriends = preg_grep_assoc($member['id'], $geeks, 'id'); // if the friend is already in the geeks list, I want to know
          if(sizeof($memberFriends) == 0){ // if the friend is NOT already in the geeks list
            $member['groups'] = array($group["id"]); // then we add an array for groups and put this group in it
            unset($member['administrator']); // since we don't need to know if the member is an admin
            $geeks[] = $member; // add the member to the array of geek friends
          }else{ // otherwise, let's update it just a bit
            $memberFriends[0]['groups'][] = $group["id"]; // add this group to the array of groups this member is in.
          }
        }
      }

/* // the original method plugged the group members ids into an array for searching. We're going to avoid doing this every time by putting the friends list into the array
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
*/
    }
    
//    echo "<pre>";
//    var_dump($geeks);
//    echo "</pre>";
    
    $percentage = (sizeof($geeks) / sizeof($friends)) * 100;



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


<?php
  }



// utility functions

// Use curl to download something from the web and return it as a string
function getStuff($url){
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $response = curl_exec($ch);
  curl_close($ch);
  return $response;
}

// Determine whether a var is "Good To Go" by checking if it is (a) set and (b) matches a given regexp (default simply looks for non-whitespace)
function gtg(){
  $val = func_get_arg(0);
  $test = func_num_args() > 1 ? func_get_arg(1) : "/\\S/";
  return isset($val) && preg_match($test, $val);
}

// Given an array of associate arrays ($assoc), checks to see if the value of $assoc[$key] matches $pattern
function preg_grep_assoc($pattern, $arr, $key){
  $returnArray;
  if(!preg_match("/^\/.*\/\$/", $pattern)){
    $pattern = "/^" . $pattern . "\$/";
  }
  foreach($arr as $assoc){
    if(preg_match($pattern, $assoc[$key])){
      $returnArray[] = $assoc;
    }
  }
  return $returnArray;
}