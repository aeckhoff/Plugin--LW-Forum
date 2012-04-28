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

class lw_forumList extends lw_forumBase
{
    function __construct()
    {
        parent::__construct();
    }
    
    function execute() 
    {
        if ($this->request->getInt("config")==1) {
            $this->buildConfig();
        }
        else {
            $this->buildList();
        }
        
    }
    
    function getOutput() 
    {
        if ($this->inAuth->isLoggedIn()) {
            return $this->output;
        }
        else {
            return "please login first";
        }   
    }
    
    function buildList()
    {
        $view = new lw_view(dirname(__FILE__).'/../templates/forumlist.phtml');
        $view->usecss = 1;
        $view->dh = $this->dh;
        $view->userID = $this->inAuth->getUserdata('id');
        $view->isAdmin = lw_registry::getInstance()->getEntry("auth")->isAllowed('admin');
        $view->configurl = lw_page::getInstance()->getUrl(array("config"=>1));
        $view->showMiniStatistic = $this->showMiniStatistic();
        $view->showCategories = $this->showCategories();
        $view->showThreadRead = $this->showThreadRead();
        $this->output = $view->render();
    }
    
    function buildConfig() {
        $allowed = lw_registry::getInstance()->getEntry("auth")->isAllowed('admin');
        if ($allowed == 1) {
            if ($this->request->getInt("save") == 1) {
                $data['lwf_read'] = $this->request->getInt("lwf_read");
                $data['lwf_replies'] = $this->request->getInt("lwf_replies");
                $data['lwf_statistic'] = $this->request->getInt("lwf_statistic");
                $data['lwf_numberofposts'] = $this->request->getInt("lwf_numberofposts");
                $data['lwf_categories'] = $this->request->getInt("lwf_categories");
                $ok = $this->dh->saveForumConfig($this->forumId, $data);
                $this->reloadPage(lw_page::getInstance()->getUrl(array("config"=>1)));
                exit();
            }
            $view = new lw_view(dirname(__FILE__).'/../templates/forumconfig.phtml');
            $view->usecss = 1;
            $view->data = $this->dh->loadForumConfig($this->forumId);
            $view->configsaveurl = lw_page::getInstance()->getUrl(array("config"=>1, "save" => 1));
            $view->forumlisturl = lw_page::getInstance()->getUrl();
            $this->output = $view->render();
        }
    }
}
