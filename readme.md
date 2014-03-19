#OVERVIEW

##General Idea:
Three Geeks Facebook app was created by Eric Hemesath, Jeremy R. Geerdes, and Justin Stevens during a Web Dojo challenge at a Web Geeks event. The idea of the Three Geeks app is to show users how many of their Facebook friends are involved with a user group in Iowa.


###To Do:

  * Back-end Dev --
      * Check user's friend list against all known Iowa user groups
        * I have been noticing that a number of the user groups do NOT have Facebook groups. Some have pages only. But I don't know that we can get a list of likes on a Facebook page unless we're administrators of the page.
  * On the front-end -- 
      * Section out all user groups
      * Show user group logo
      * Total count of members
      * Show percentage of how geeky your friend list
      * Visually display users geeky-ness in a chart or graph
        * Do we use an OSS charting library or build our own in <canvas>?
  * Other --
      * Do we handle friends that are in multiple user groups different?
        * Jeremy votes that we list the friends under the group they belong to, but we only count them once toward the geek quotient.
      * Can we show how long a friend has been a member of a group
      * Add a button to share this app with your friends
      * Might be a good idea to display all available user groups even if the user does not know any members
      * Include link to member groups website, or Facebook group
      * Link each friend's photo and name to their Facebook page
      * Display each friend's statues (might need to show this on hover or truncate the copy to save space)
      * Ability to petition devs to add more user groups
      * Suggest friends
      * Button to join a given group (just in case they missed it before!)
