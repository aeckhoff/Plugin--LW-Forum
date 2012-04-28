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

class lw_forumBackend extends lw_forumBase
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function execute() 
    {
        if ($this->request->getAlnum("pcmd") == "assignModerators") {
            $this->assignIntranets();
            exit();
        }
        if ($this->request->getAlnum("pcmd") == "saveAssignModerators") {
            $this->saveAssignIntranets();
            exit();
        }
        if ($this->request->getAlnum("pcmd") == "save") {
            $parameter['lwf_read'] = $this->request->getRaw("lwf_read");
            $parameter['lwf_replies'] = $this->request->getRaw("lwf_replies");
            $parameter['lwf_statistic'] = $this->request->getRaw("lwf_statistic");
            $parameter['lwf_numberofposts'] = $this->request->getRaw("lwf_numberofposts");
            $this->dh->savePluginData('lw_listtool', lw_page::getInstance()->getId(), $parameter, $content);
            $this->pageReload($this->buildURL(array("saved" => 1), array("pcmd")));
        }        
        
        $view = new lw_view(dirname(__FILE__).'/../templates/backend.phtml');
        if (method_exists($this, 'getRightsLinks')) {
            $view->rightslink = $this->getRightslinks();
        } 
        else {
            $view->rightslink = '<a href="#" onClick="openNewWindow(\''.lw_object::buildUrl(array("pcmd" => "assignModerators", "ltid" => lw_page::getInstance()->getId())) . '\');">Rechtezuweisung</a>';
        }
        
        if (method_exists($this, 'getRightsList')) {
            $view->rightslist = $this->getRightslist();
        } 
        else {
            include_once($this->config['path']['server'] . 'c_backend/intranetassignments/agent_intranetassignments.class.php');
            $assign = new agent_intranetassignments();
            $assign->setObject('listtool_cbox', lw_page::getInstance()->getId());
            $view->intranets = $this->dh->getAllAssignedIntranetsByObject('lw_forum_moderators', lw_page::getInstance()->getId());
            $view->users = $this->dh->getAllAssignedUsersByObject('lw_forum_moderators', lw_page::getInstance()->getId());
        }
        
        $data = $this->dh->loadPluginData('lw_listtool', lw_page::getInstance()->getId());
        $view->data = $data['parameter'];
        $view->actionurl = lw_object::buildUrl(array("pcmd" => "save"));
        $this->output = $view->render();
    }
    
    public function saveAssignIntranets()
    {
        include_once($this->config['path']['server'].'c_backend/intranetassignments/agent_intranetassignments.class.php');
        $assign = new agent_intranetassignments();
        $assign->setDelegate($this);
        $assign->setObject('lw_forum_moderators', lw_page::getInstance()->getId());

        $temp = $this->request->getPostArray();
        $assign->setAssignedUsers($temp['user']);
        $assign->setAssignedIntranets($temp['intranet']);
        $assign->saveObject();

        $this->pageReload(lw_object::buildUrl(array("pcmd" => "assignModerators", "ltid" => lw_page::getInstance()->getId())));
    }

    public function assignIntranets()
    {
        include_once($this->config['path']['server'].'c_backend/intranetassignments/agent_intranetassignments.class.php');
        $assign = new agent_intranetassignments();

        $assign->setObject('lw_forum_moderators', lw_page::getInstance()->getId());
        $assign->setAction(lw_object::buildUrl(array("pcmd" => "saveAssignModerators", "ltid" => lw_page::getInstance()->getId())));
        $assign->execute();
    }    
    
    function getOutput() 
    {
        return $this->output;
    }    
}
