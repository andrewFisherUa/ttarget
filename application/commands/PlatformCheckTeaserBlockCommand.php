<?php
/*******************************************************************
*	file: PlatformCheckTeaserBlockCommand.php
*	freated: 19 июня 2015 г. - 16:42:58
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/


class PlatformCheckTeaserBlockCommand extends CConsoleCommand
{
	public function actionIndex()
	{
		
		
		//Get all active platforms
		$Platforms = Platforms::model()->findAll('is_active = 1');
		
		echo "Validating platforms...\n";
		
		foreach($Platforms as $Platform){
			$_urls = preg_split("/\n/", $Platform->hosts, -1, PREG_SPLIT_NO_EMPTY);
			echo "Validating platform: [{$Platform->server}]\n";
			echo "\tValidating platform hosts (".count($_urls).")...\n";
			foreach($_urls as $_url){
				
				$_url = 'http://'.$_url;
				
				$this->checkUrl($_url);
			}
			echo "\n";
		}
	}
	
	protected function checkUrl( $url )
	{
		if($this->validateUrl($url)){
			echo "\tSending request to: {$url}.....";
			try{
				$_content = $this->getContent($url);
				if(!empty($_content)){
					if($this->detectTeaserBlock($_content, '')){
						echo "Ok\n";
					} else {
						echo "Fail\n";
					}
				} else {
					echo "Fail (content is empty)\n";
				}
			} catch(Exception $e) {
				echo "Fail ({$e->getMessage()})\n";
			}
		} else {
			echo "\tPassing url: {$url}.....\n";
		}
	}
	
	protected function validateUrl( $url )
	{
		return preg_match("/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/", $url);
	}
	
	protected function getContent( $url )
	{
		$_headers = $this->_getResponseHeaders($url);
		if(!empty($_headers[0])){
			$_status  = substr($_headers[0], 9, 3);
			if($_status == '200' || $_status == '301' || $_status == '302'){
				$_content = @file_get_contents($url);
				return $_content;
			} else {
				throw new Exception("Unable get url '{$url}': response code is [{$_status}]");
			}
		} else {
			throw new Exception("Unable get url '{$url}': response is empty");
		}
	}
	
	protected function _getResponseHeaders( $url )
	{
		return get_headers($url);
	}
	
	protected function detectTeaserBlock( $content, $pattern = '' )
	{
		return true;
	}
	
	
}



/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: PlatformCheckTeaserBlockCommand.php
**/