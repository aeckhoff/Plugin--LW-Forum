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

class lw_forum extends lw_plugin
{
    function __construct()
    {
        parent::__construct();
    }
    
    function buildPageOutput() 
    {
        if ($this->params['mode'] == "threadlist") {
            include_once(dirname(__FILE__).'/classes/lw_forumThreadlist.php');
            $forum = new lw_forumThreadlist($this->request->getIndex());
            $forum->setRequest($this->request);
            $forum->setResponse($this->response);
            $forum->setConfig($this->config);
            $forum->setForumId($this->params['id']);
            $forum->setInAuth(lw_in_auth::getInstance());
            $forum->execute();
            return $forum->getOutput();
        }
        if ($this->params['mode'] == "categorylist") {
            include_once(dirname(__FILE__).'/classes/lw_forumCategorylist.php');
            $forum = new lw_forumCategorylist($this->request->getIndex());
            $forum->setRequest($this->request);
            $forum->setResponse($this->response);
            $forum->setConfig($this->config);
            $forum->setForumId($this->params['id']);
            $forum->setInAuth(lw_in_auth::getInstance());
            $forum->execute();
            return $forum->getOutput();
        }
        include_once(dirname(__FILE__).'/classes/lw_forumList.php');
        $forum = new lw_forumList($this->request->getIndex());
        $forum->setRequest($this->request);
        $forum->setResponse($this->response);
        $forum->setConfig($this->config);
        $forum->setForumId($this->params['id']);
        $forum->setInAuth(lw_in_auth::getInstance());
        $forum->execute();
        return $forum->getOutput();
    }
    
    public function getOutput()
    {
        include_once(dirname(__FILE__).'/classes/lw_forumBackend.php');
        $backend = new lw_forumBackend($this->request->getIndex());
        $backend->setRequest($this->request);
        $backend->setResponse($this->response);
        $backend->setConfig($this->config);
        $backend->setInAuth(lw_in_auth::getInstance());
        $backend->execute();
        return $backend->getOutput();
    }
}    
