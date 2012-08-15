<?php

// Initial setup stuff
$app_id = "494168277279995";
$canvas_page = "http://threegeeks.debraheightswesleyan.org/";
$auth_url = "http://www.facebook.com/dialog/oauth?client_id="
  . $app_id . "&redirect_uri=" . urlencode($canvas_page);
    
$signed_request = $_REQUEST["signed_request"];
list($encoded_sig, $payload) = explode('.', $signed_request, 2);
  
$data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
  



?>
<!DOCTYPE html>
<html>
  <head>
    <title>Three Geeks DSM Web Geeks Geeky-ness meter</title>
<?php




// If the user is not logged in, we redirect to the login page.
if(empty($data['user_id'])){
  echo "<script type=\"text/javascript\">top.location.href = '" . $auth_url . "'</script>";
}else{
}




?>
    <style>
      @import url(http://fonts.googleapis.com/css?family=Advent+Pro);
      @import url(/master.css);
    </style>
  </head>
  <body>
    <div id="wrapper">
<?php




if(empty($data['user_id'])){ // Let's display a fallback text, just in case they don't have JS enabled for the redirect




?>
    <h1>Oops!</h1>
    <p>You don&apos;t appear to be logged in! If you haven&apos;t been redirected to the login page, <a href="<?php echo $auth_url; ?>">click here now</a>!</p>
<?php




}else{ // Now we can get to work!
  // Initial work
  $base_url = "https://graph.facebook.com/";
  $base_q = "?access_token=" . $data['oauth_token'];
  
  
  // get the user's friends list
  $frs = json_decode(getStuff($base_url . "me/friends" . $base_q), true);
  $friends = array();
  foreach($frs['data'] as $friend){
    $friends[] = $fr['id'];
  }
  
  
  // Read and parse the JSON file containing the geeky groups we want to check
  $groups = json_decode(file_get_contents("groups.json"), true);

  // Run through the groups and check the membership against user's friend list
  $geeks = array();
  $listHtml = '';
  foreach($groups as $group){
    if(!gtg($group['id'], "/^\\d+$/")){ // if I don't have a valid id
      continue;
    }
    
    $group['memberFriends'] = array();
    $listHtml .= '<div class="group"><h2 class="groupTitle"><a href="http://www.facebook.com/' . $group['id'] . '">' . $group['name'] . '</a></h2><ul class="groupList">';
    $members = json_decode(getStuff($base_url . $group['id'] . "/members" . $base_q), true); // get and parse the members list
    foreach($members['data'] as $member){
      if(sizeof(preg_grep_assoc($member['id'], $frs['data'], 'id')) > 0){ // if the group member is in the friend list
        $memberFriends = preg_grep_assoc($member['id'], $geeks, 'id'); // if the friend is already in the geeks list, I want to know!
        if(sizeof($memberFriends) == 0){ // if the friend is NOT already in the geeks list
          $member['groups'] = array($group['id']); // the we add an array for groups and put this group in it
          unset($member['administrator']); // since we don't need to know if the member is an admin
          $geeks[] = $group['memberFriends'][] = $member; // add the member to the array of geek friends and the group's array of member friends
        }else{ // else if the friend IS in the geeks list
          $memberFriends[0]['groups'][] = $group['id']; // add this group to the array of groups this member is in
          $group['memberFriends'][] = $member; // add this member to the group's array of member friends
        }
        
        // Let's write the html for this
        $listHtml .= '<li><img src="https://graph.facebook.com/' . $member['id'] . '/picture" class="photo" /><div class="copy"><p class="name"><a href="http://www.facebook.com/' . $member['id'] . '">' . $member['name'] . '</a></p></div></li>';
      }
    }
    $listHtml .= "</ul></div>\n";
  }
  
  $percentage = (sizeof($geeks) / sizeof($friends)) * 100;



?>
    <img src="http://www.dsmwebgeeks.com/wp-content/themes/dsmweb2012/images/webgeekslogo.png" id="logo">
    <div id="geek-level">
      <p>Your friend list is <?php echo number_format($percentage, 2); ?>% (<?php echo sizeof($geeks) . " / " . sizeof($frs['data']); ?>) geeky.</p>
    </div>
    
    <h1 id="page-title">YourGeekyFriends/</h1>
<?php


  echo $listHtml;
} // And we're done!


?>
    </div>
  </body>
</html>
<?php




// Utility functions


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

?>