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

include_once(dirname(__FILE__).'/lw_forumBase.php');

class lw_forumUser extends lw_forumBase
{
    
    private static $instance = array();
    
    public function __construct($id)
    {
        parent::__construct();
        $this->id = intval($id);
        $this->init();
    }
    
    public function init()
    {
        $this->user = $this->dh->loadUserDataById($this->id);
        $this->numberofposts = $this->dh->getNumberOfPostsByUser($this->id);
    }
    
    public function getLwForumUserObjectById($id)
    {
        if(self::$instance[$id] == null) {
            self::$instance[$id] = new lw_forumUser($id);
        }
        return self::$instance[$id];
    }
    
    public function getNumberOfPosts() 
    {
        return $this->numberofposts;
    }
    
    public function getName() 
    {
        return $this->user['name'];
    }
    
}
