<?php
//xFacility2014
//xFYoutube
//Studio2b
//Michael Son
//02DEC2014(1.0.0.) - Newly added.
class XFYoutube extends XFObject {
	var $token;
	var $activities, $channels, $playlists, $playlistItmes;
	
	function XFYoutube($token=null) {
		$this->activities = new XFYoutubeActivities($token);
		$this->channels = new XFYoutubeChannels($token);
		$this->playlists = new XFYoutubePlaylists($token);
		$this->playlistItems = new XFYoutubePlaylistItems($token);
		$this->videos = new XFYoutubeVideos($token);
	}
	
	//Custom
	function getChannelPlaylists($categoryId=NULL, $forUsername=NULL, $id=NULL, $managedByMe=NULL) {
		if(!is_null($categoryId) || !is_null($forUsername) || !is_null($id) || !is_null($managedByMe)) {
			//Get channel Info. and a recents playlist.
			$channel = $this->channels->lists("id,contentDetails", $categoryId, $forUsername, $id, $managedByMe);
			$return[0][id] = $channel[items][0][contentDetails][relatedPlaylists][uploads];
			$return[0][title] = "[=recents]";
			$return[0][channelId] = $channel[items][0][id];
			$channelId = $channel[items][0][id];
			//Get playlists
			while(true) {
				$playlists = $this->playlists->lists(null, $channelId, null, null, 50, null, null, $pageToken);
				$pos = count($return);
				foreach($playlists[items] as $key => $value) {
					$return[$key+$pos][id] = $value[id];
					$return[$key+$pos][title] = $value[snippet][title];
					$return[$key+$pos][channelId] = $value[snippet][channelId];
				}
				if(is_null($playlists[nextPageToken])) {
					break;
				} else {
					$pageToken = $playlists[nextPageToken];
				}
			}
		} else {
			$return = false;
		}
		return $return;
	}
}
?>