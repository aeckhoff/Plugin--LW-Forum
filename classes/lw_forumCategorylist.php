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

class lw_forumCategorylist extends lw_forumBase
{
    function __construct()
    {
        parent::__construct();
    }
    
    function execute() 
    {
        $this->buildList();
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
        $view = new lw_view(dirname(__FILE__).'/../templates/categorylist.phtml');
        $view->usecss = 1;
        $view->dh = $this->dh;
        $view->categoryName = lw_page::getInstance()->getPageValue('name');
        $view->categoryID = lw_page::getInstance()->getId();
        $view->forumID = lw_page::getInstance()->getPageValue('relation');
        $view->userID = $this->inAuth->getUserdata('id');
        $view->showMiniStatistic = $this->showMiniStatistic();
        $view->showThreadRead = $this->showThreadRead();
        $this->output = $view->render();
    }
}
