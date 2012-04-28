<?php

/**************************************************************************
*  Copyright notice
*
*  Copyright 2012 Logic Works GmbH
*
*  Licensed under the Apache License, Version 2.0 (the "License");
*  you may not use this file except in compliance with the License.
*  You may obtain a copy of the License at
*
*  http://www.apache.org/licenses/LICENSE-2.0
*  
*  Unless required by applicable law or agreed to in writing, software
*  distributed under the License is distributed on an "AS IS" BASIS,
*  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*  See the License for the specific language governing permissions and
*  limitations under the License.
*  
***************************************************************************/

class lw_forumBase extends lw_object
{
    function __construct()
    {
        include_once(dirname(__FILE__).'/lw_forumDatahandler.php');
        include_once(dirname(__FILE__).'/lw_forumUser.php');
        $this->dh = new lw_forumDatahandler();
    }
    
    public function setRequest($request)
    {
        $this->request = $request;
    }
    
    public function setResponse($response)
    {
        $this->response = $response;
    }
    
    public function setConfig($config)
    {
        $this->config = $config;
    }
    
    public function setInAuth($inAuth)
    {
        $this->inAuth = $inAuth;
    }
    
    public function setForumId($id)
    {
        $this->forumId = $id;
        $this->forumConfig = $this->dh->loadForumConfig($id);
    }
    
    public function showThreadRead() {
        if ($this->forumConfig['opt1number']==1) {
            return true;
        }
        return false;
    }
    
    public function showReplies() {
        if ($this->forumConfig['opt2number']==1) {
            return true;
        }
        return false;
    }
    
    public function showMiniStatistic() {
        if ($this->forumConfig['opt3number']==1) {
            return true;
        }
        return false;
    }
    
    public function showNumberOfPostsForUser() {
        if ($this->forumConfig['opt4number']==1) {
            return true;
        }
        return false;
    }
    
    public function showCategories() {
        if ($this->forumConfig['opt5number']==1) {
            return true;
        }
        return false;
    }
    
    public function reloadPage($url) 
    {
        $url = str_replace("&amp;", "&", $url);
        //$this->forceReload($url);
        echo '<html>' . PHP_EOL;
        echo '    <head><meta http-equiv="Refresh" content="0;url=' . $url . '" /></head>' . PHP_EOL;
        echo '    <body onload="try {self.location.href=' . "'" . $url . "'" . ' } catch(e) {}"><a href="' . $url . '">Redirect </a></body>' . PHP_EOL;
        echo '</html>' . PHP_EOL;
        exit();        
    }
    
    public function isModerator() 
    {
        $inAuthOK = $this->inAuth->isObjectAllowed('lw_forum_moderators', lw_page::getInstance()->getId());
        $hasAssignments = $this->inAuth->hasObjectAssignments('lw_forum_moderators', lw_page::getInstance()->getId());
        if ($inAuthOK && $hasAssignments) return true;
        return false;        
    }
}
