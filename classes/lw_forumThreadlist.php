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

class lw_forumThreadlist extends lw_forumBase
{
    function __construct()
    {
        parent::__construct();
    }
    
    function execute() 
    {
        if ($this->inAuth->isLoggedIn()) {
            switch($this->request->getAlnum('fcmd')) {
                case "newTopic":
                    $this->buildNewTopicForm();
                    break;

                default:
                    if ($this->request->getInt('topicID')>0) {
                        $this->buildEntrylistByThreadId($this->request->getInt('topicID'));
                    }
                    else {
                        $this->buildThreadlist();
                    }
                    break;
            }
        }
        else {
            $this->output = "please login first";
        }
    }
    
    function getOutput() 
    {
        return $this->output;
    }
    
    function buildThreadlist()
    {
        $view = new lw_view(dirname(__FILE__).'/../templates/threadlist.phtml');
        $view->topics = $this->dh->getThreadlist(lw_page::getInstance()->getId());
        $view->newtopicurl = lw_page::getInstance()->getUrl(array("fcmd"=>"newTopic"));
        $view->usecss = 1;
        $view->dh = $this->dh;
        $view->threadID = lw_page::getInstance()->getId();
        $view->categoryID = lw_page::getInstance()->getPageValue('relation');
        $view->categoryName = lw_page::getInstance($view->categoryID)->getPageValue('name');
        $view->forumID = lw_page::getInstance($view->categoryID)->getPageValue('relation');
        $view->threadName = lw_page::getInstance()->getPageValue('name');
        $view->userID = $this->inAuth->getUserdata('id');
        $view->showMiniStatistic = $this->showMiniStatistic();
        $view->showReplies = $this->showReplies();
        $view->showThreadRead = $this->showThreadRead();
        $view->showNumberOfPostsForUser = $this->showNumberOfPostsForUser();
        $view->showCategories = $this->showCategories();
        $this->output = $view->render();
    }
    
    function buildEntrylistByThreadId($id)
    {
        if ($this->request->getInt('edit') > 0) {
            $this->editEntry($id, $this->request->getInt('edit'));
            return true;
        }
        if ($this->request->getInt('save') == 1) {
            $data = $this->prepareData();
            if (!$this->error) {
                $this->saveNewEntry($id, $data);
                $this->reloadPage(lw_page::getInstance()->getUrl(array("topicID"=>$id)));
            } 
        }
        if ($this->request->getInt('delete') > 0) {
            $post = $this->dh->getPostById($this->request->getInt('delete'));
            if ($post['opt1number'] == $this->inAuth->getUserdata('id') || $this->isModerator()) {
                $main = $this->dh->deletePostById($this->request->getInt('delete'), $post['opt1bool']);
            }
            if ($main == true) {
                $this->reloadPage(lw_page::getInstance()->getUrl());
            } else {
                $this->reloadPage(lw_page::getInstance()->getUrl(array("topicID"=>$id)));
            }
        }
        $view = new lw_view(dirname(__FILE__).'/../templates/entrylist.phtml');
        $view->entries = $this->dh->getEntrylistByThreadId(lw_page::getInstance()->getId(), $id);
        if (count($view->entries)<1) {
            $this->reloadPage(lw_page::getInstance()->getUrl());
        }
        $view->threadlisturl = lw_page::getInstance()->getUrl();
        $view->savenewreplyurl = lw_page::getInstance()->getUrl(array("topicID"=>$id, "save"=>1));
        $view->usecss = 1;
        $view->actualUserId = $this->inAuth->getUserdata('id');
        $view->topicID = $id;
        $view->isModerator = $this->isModerator();
        $view->showNumberOfPostsForUser = $this->showNumberOfPostsForUser();
        $this->output = $view->render();
        $this->dh->markThreadAsRead($id, $this->inAuth->getUserdata('id'));
    }
    
    function editEntry($threadId, $entryId) {
        $lwmaster = new lw_lwmaster_entry(lw_registry::getInstance()->getEntry("repository")->getRepository('lwmaster'), $entryId);
        if ($this->request->getInt('save') == 1) {
            $data = $this->prepareData();
            if (!$this->error) {
                $lwmaster->setValue("name", $data['title']);
                $lwmaster->setValue("opt1clob", $data['text']);
                $lwmaster->setValue("lw_last_date", date("YmdHis"));
                $lwmaster->setValue("lw_last_user", $this->inAuth->getUserdata('id'));
                if ($this->isModerator() && $lwmaster->getValue('opt1number') != $this->inAuth->getUserdata('id'))  {
                    $lwmaster->setValue("opt2bool", 1);
                }
                $lwmaster->save();
                $this->reloadPage(lw_page::getInstance()->getUrl(array("topicID"=>$threadId)));
            }             
        }
        $view = new lw_view(dirname(__FILE__).'/../templates/topicform.phtml');
        $view->savenewtopicurl = lw_page::getInstance()->getUrl(array("topicID"=>$threadId, "fcmd"=>"editTopic", "save"=>1, "edit" => $entryId));
        $view->threadlisturl = lw_page::getInstance()->getUrl();
        $view->headline = "Edit Entry";
        $view->title = lw_filter::striptags($lwmaster->getValue('name'));
        $view->text = lw_filter::striptags($lwmaster->getValue('opt1clob'));
        $this->output = $view->render();
    }
    
    function buildNewTopicForm()
    {
        if ($this->request->getInt('save') == 1) {
            $data = $this->prepareData();
            if (!$this->error) {
                $id = $this->saveNewThread($data);
                $this->reloadPage(lw_page::getInstance()->getUrl(array("topicID"=>$id)));
            } 
        }
        $view = new lw_view(dirname(__FILE__).'/../templates/topicform.phtml');
        $view->savenewtopicurl = lw_page::getInstance()->getUrl(array("fcmd"=>"newTopic", "save"=>1));
        $view->threadlisturl = lw_page::getInstance()->getUrl();
        $view->headline = "New Topic";
        $view->title = lw_filter::htmlentities(lw_filter::striptags($data['title']));
        $view->text = nl2br(lw_filter::htmlentities(lw_filter::striptags($data['text'])));
        $this->output = $view->render();
    }
    
    function prepareData()
    {
        $data['title'] = lw_filter::htmlentities(trim(lw_filter::visible(lw_filter::striptags($this->request->getRaw('title')))));
        if (strlen(trim($data['title'])) > 255) {
            $this->error = true;
        }
        $data['text'] = lw_filter::htmlentities(trim(lw_filter::striptags($this->request->getRaw('text'))));
        return $data;
    }
    
    function saveNewThread($data) 
    {
        if (strlen($data['text'])>0 ) {
            $save['lw_object'] = 'lwforum_entry';
            $save['name'] = $data['title'];
            $save['opt1clob'] = $data['text'];
            $save['category_id'] = lw_page::getInstance()->getId();
            $save['lw_first_date'] = date('YmdHis');
            $save['lw_last_date'] = date('YmdHis');
            $save['lw_version'] = 1;
            $save['opt1bool'] = 1;
            $save['opt1number'] = $this->inAuth->getUserdata('id');
            $lwmaster = new lw_lwmaster_entry(lw_registry::getInstance()->getEntry("repository")->getRepository('lwmaster'), false);
            $lwmaster->setValues($save);
            $lwmaster->save();
            return $lwmaster->getId();
        }
    }
    
    function saveNewEntry($id, $data) 
    {
        if (strlen($data['text'])>0 ) {
            $save['lw_object'] = 'lwforum_entry';
            $save['name'] = $data['title'];
            $save['opt1clob'] = $data['text'];
            $save['category_id'] = lw_page::getInstance()->getId();
            $save['lw_first_date'] = date('YmdHis');
            $save['lw_last_date'] = date('YmdHis');
            $save['lw_version'] = 1;
            $save['opt1number'] = $this->inAuth->getUserdata('id'); // User ID
            $save['opt2number'] = $id; // Thread ID
            $lwmaster = new lw_lwmaster_entry(lw_registry::getInstance()->getEntry("repository")->getRepository('lwmaster'), false);
            $lwmaster->setValues($save);
            $lwmaster->save();
            $entryid = $lwmaster->getId();
            
            $this->dh->setThreadAsUnread($id);
        }
    }    
    
}
