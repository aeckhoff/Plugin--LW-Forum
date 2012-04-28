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

class lw_forumDatahandler
{
    public function __construct()
    {
        $this->db = lw_registry::getInstance()->getEntry("db");
        $this->entryObjectIdentifier = 'lwforum_entry';
        $this->lwmasterRepository = lw_registry::getInstance()->getEntry("repository")->getRepository('lwmaster');
    }
    
    public function loadForumConfig($id) {
        $this->db->setStatement("SELECT * FROM t:lw_master WHERE lw_object = :lwobject AND name = :name");
        $this->db->bindParameter('lwobject', 's', 'lw_forum_config');
        $this->db->bindParameter('name', 's', $id);
        return $this->db->pselect1();
    }
    
    public function saveForumConfig($id, $data) {
        $olddata = $this->loadForumConfig($id);
        if ($olddata['id']>0) {
            $this->db->setStatement("UPDATE t:lw_master SET opt1number = :opt1number, opt2number = :opt2number, opt3number = :opt3number, opt4number = :opt4number, opt5number = :opt5number WHERE lw_object = :lwobject AND name = :name");
        }
        else {
            $this->db->setStatement("INSERT INTO t:lw_master (opt1number, opt2number, opt3number, opt4number, opt5number, lw_object, name) VALUES (:opt1number, :opt2number, :opt3number, :opt4number, :opt5number, :lwobject, :name) ");
        }
        $this->db->bindParameter('lwobject', 's', 'lw_forum_config');
        $this->db->bindParameter('name', 's', $id);
        $this->db->bindParameter('opt1number', 'i', $data['lwf_read']);
        $this->db->bindParameter('opt2number', 'i', $data['lwf_replies']);
        $this->db->bindParameter('opt3number', 'i', $data['lwf_statistic']);
        $this->db->bindParameter('opt4number', 'i', $data['lwf_numberofposts']);
        $this->db->bindParameter('opt5number', 'i', $data['lwf_categories']);
        return $this->db->pdbquery();
    }
    
    public function getThreadlist($catid) 
    {
        $this->db->setStatement("SELECT * FROM t:lw_master WHERE lw_object = :lwobject AND category_id = :catid AND opt1bool = 1 ORDER BY lw_first_date DESC");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('catid', 'i', $catid);
        return $this->db->pselect();
    }
    
    public function getRepliesByTopicID($id)
    {
        $this->db->setStatement("SELECT count(id) as quantity FROM t:lw_master WHERE lw_object = :lwobject AND opt2number = :id");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('id', 'i', $id);
        $result = $this->db->pselect1();
        return $result['quantity'];
    }
    
    public function getEntrylistByThreadId($catid, $id)
    {
        $this->db->setStatement("SELECT * FROM t:lw_master WHERE lw_object = :lwobject AND category_id = :catid AND ( opt2number = :id OR id = :id) ORDER BY lw_first_date ASC");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('catid', 'i', $catid);
        $this->db->bindParameter('id', 'i', $id);
        return $this->db->pselect();
    }
    
    public function getLastPostByTopicID($id)
    {
        $this->db->setStatement("SELECT t:lw_master.*, t:lw_in_user.name username FROM t:lw_master, t:lw_in_user WHERE t:lw_master.lw_object = :lwobject AND ( t:lw_master.opt2number = :id OR t:lw_master.id = :id) AND t:lw_master.opt1number = t:lw_in_user.id ORDER BY t:lw_master.lw_first_date DESC");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('id', 'i', $id);
        return $this->db->pselect1();
    }
    
    public function getLastPostByThreadID($id)
    {
        $this->db->setStatement("SELECT * FROM t:lw_master WHERE lw_object = :lwobject AND category_id = :id ORDER BY lw_first_date DESC");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('id', 'i', $id);
        return $this->db->pselect1();
    }
    
    public function loadUserDataById($id)
    {
        $this->db->setStatement("SELECT * FROM t:lw_in_user WHERE id = :id");
        $this->db->bindParameter('id', 'i', $id);
        return $this->db->pselect1();
    }

    public function getNumberOfPostsByUser($id)
    {
        $this->db->setStatement("SELECT count(id) as quantity FROM t:lw_master WHERE opt1number = :id AND lw_object = :lwobject ");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('id', 'i', $id);
        $result = $this->db->pselect1();
        return $result['quantity'];
    }
    
    public function getPostById($id) {
        $this->db->setStatement("SELECT * FROM t:lw_master WHERE id = :id AND lw_object = :lwobject ");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('id', 'i', $id);
        return $this->db->pselect1();
    }
    
    public function deletePostById($id, $starter=false)
    {
        $quantity = $this->getRepliesByTopicID($id);
        if ($starter != 1 || $quantity < 1) {
            $this->db->setStatement("DELETE FROM t:lw_master WHERE id = :id AND lw_object = :lwobject ");
            $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
            $this->db->bindParameter('id', 'i', $id);
            $ok = $this->db->pdbquery();
            if ($starter == 1) {
                return true;
            }
            return false;
        }
        else {
            $this->db->setStatement("UPDATE t:lw_master SET name = :name, opt1clob = :text WHERE id = :id AND lw_object = :lwobject ");
            $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
            $this->db->bindParameter('name', 's', 'deleted post');
            $this->db->bindParameter('text', 's', 'no text');
            $this->db->bindParameter('id', 'i', $id);
            $ok = $this->db->pdbquery();
            return false;
        }
    }
    
    public function getTotalNumberOfThreads() 
    {
        $this->db->setStatement("SELECT count(id) as quantity FROM t:lw_master WHERE opt1bool = 1 AND lw_object = :lwobject ");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $result = $this->db->pselect1();
        return $result['quantity'];        
    }    
    
    public function getTotalNumberOfPosts() 
    {
        $this->db->setStatement("SELECT count(id) as quantity FROM t:lw_master WHERE lw_object = :lwobject ");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $result = $this->db->pselect1();
        return $result['quantity'];        
    }
    
    public function getTotalNumberOfTopicsByThread($id) 
    {
        $this->db->setStatement("SELECT count(id) as quantity FROM t:lw_master WHERE opt1bool = 1 AND lw_object = :lwobject AND category_id = :id");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('id', 'i', $id);
        $result = $this->db->pselect1();
        return $result['quantity'];        
    }    
    
    public function getTotalNumberOfPostsByThread($id) 
    {
        $this->db->setStatement("SELECT count(id) as quantity FROM t:lw_master WHERE lw_object = :lwobject AND category_id = :id");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('id', 'i', $id);
        $result = $this->db->pselect1();
        return $result['quantity'];        
    }
    
    public function moveThreadToCategory($threadID, $categoryID) 
    {
        $this->db->setStatement("UPDATE t:lw_master SET category_id = :catid WHERE lw_object = :lwobject AND (id = :threadid OR opt2number = :threadid) ");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('threadid', 'i', $threadID);
        $this->db->bindParameter('catid', 'i', $categoryID);
        return $this->db->pdbquery();
    }
    
    public function getAllCategoriesByForumId($id) 
    {
        $this->db->setStatement("SELECT t:lw_pages.id, t:lw_pages.name FROM t:lw_pages, t:lw_pagemeta WHERE t:lw_pages.relation = :id AND t:lw_pages.id = t:lw_pagemeta.id AND t:lw_pagemeta.keywords like '%lw_forum_category%'");
        $this->db->bindParameter('id', 'i', $id);
        return $this->db->pselect();
    }
    
    public function getAllThreadsByCategoryId($id)
    {
        $this->db->setStatement("SELECT t:lw_pages.id, t:lw_pages.name FROM t:lw_pages, t:lw_pagemeta WHERE t:lw_pages.relation = :id AND t:lw_pages.id = t:lw_pagemeta.id AND t:lw_pagemeta.keywords like '%lw_forum_thread%'");
        $this->db->bindParameter('id', 'i', $id);
        return $this->db->pselect();
    }
    
    public function getAllThreads()
    {
        $this->db->setStatement("SELECT t:lw_pages.id, t:lw_pages.name FROM t:lw_pages, t:lw_pagemeta WHERE t:lw_pages.id = t:lw_pagemeta.id AND t:lw_pagemeta.keywords like '%lw_forum_thread%' ORDER BY t:lw_pages.name ASC");
        return $this->db->pselect();
    }
    
    public function getNumberOfTopicsByCategory($id)
    {
        $this->db->setStatement("SELECT count(id) as quantity FROM t:lw_master WHERE opt1bool = 1 AND lw_object = :lwobject AND category_id = :id");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('id', 'i', $id);
        $result = $this->db->pselect1();
        return $result['quantity'];        
    }
    
    public function markThreadAsRead($threadID, $userID) 
    {
        $this->db->setStatement("DELETE FROM t:lw_assignments WHERE id_a = :thread AND id_b=:user AND lw_object = :lwobject");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('thread', 'i', $threadID);
        $this->db->bindParameter('user', 'i', $userID);
        $ok = $this->db->pdbquery();
        
        $this->db->setStatement("INSERT INTO t:lw_assignments (id_a, id_b, lw_object) VALUES (:thread, :user, :lwobject)");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('thread', 'i', $threadID);
        $this->db->bindParameter('user', 'i', $userID);
        $ok = $this->db->pdbquery();
    }
    
    public function setThreadAsUnread($threadID)
    {
        $this->db->setStatement("DELETE FROM t:lw_assignments WHERE id_a = :thread AND lw_object = :lwobject");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('thread', 'i', $threadID);
        $ok = $this->db->pdbquery();
    }
    
    public function isTopicUnreadByUser($topicID, $userID)
    {
        $this->db->setStatement("SELECT * FROM t:lw_assignments WHERE id_a = :thread AND id_b=:user AND lw_object = :lwobject");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('thread', 'i', $topicID);
        $this->db->bindParameter('user', 'i', $userID);
        $result = $this->db->pselect1();
        if (count($result)==3) {
            return false;
        }
        return true;
    }
    
    public function isThreadUnreadByUser($threadID, $userID)
    {
        $this->db->setStatement("SELECT id FROM t:lw_master WHERE opt1bool = 1 AND lw_object = :lwobject AND category_id = :id");
        $this->db->bindParameter('lwobject', 's', $this->entryObjectIdentifier);
        $this->db->bindParameter('id', 'i',$threadID);
        $result = $this->db->pselect();

        $unread = false;
        
        foreach ($result as $topic) {
            if ($this->isTopicUnreadByUser($topic['id'], $userID)) {
                $unread = true;
            }
        }
        return $unread;
    }
    
    function getAllAssignedIntranetsByObject($otype, $oid)
    {
        $this->db->setStatement("SELECT t:lw_intranets.name FROM t:lw_intra_assign, t:lw_intranets WHERE t:lw_intra_assign.object_type = :objecttype AND t:lw_intra_assign.object_id = :objectid AND t:lw_intra_assign.right_type = :righttype AND t:lw_intra_assign.right_id = t:lw_intranets.id ");
        $this->db->bindParameter('objecttype', 's', $otype);
        $this->db->bindParameter('righttype', 's', 'intranet');
        $this->db->bindParameter('objectid', 'i', $oid);
        return $this->db->pselect();
    }

    function getAllAssignedUsersByObject($otype, $oid)
    {
        $this->db->setStatement("SELECT t:lw_in_user.name FROM t:lw_intra_assign, t:lw_in_user WHERE t:lw_intra_assign.object_type = :objecttype AND t:lw_intra_assign.object_id = :objectid AND t:lw_intra_assign.right_type = :righttype AND t:lw_intra_assign.right_id = t:lw_in_user.id ");
        $this->db->bindParameter('objecttype', 's', $otype);
        $this->db->bindParameter('righttype', 's', 'user');
        $this->db->bindParameter('objectid', 'i', $oid);
        return $this->db->pselect();
    }
    
    public function deleteEntryByContainer($cid)
    {
        $this->db->setStatement("DELETE FROM t:lw_plugins WHERE container_id = :cid");
        $this->db->bindParameter('cid', 'i', $cid);
        return $this->db->pdbquery();
    }

    public function loadPluginData($plugin, $cid)
    {
        $this->db->setStatement("SELECT * FROM t:lw_plugins WHERE plugin = :plugin AND container_id = :cid");
        $this->db->bindParameter('plugin', 's', $plugin);
        $this->db->bindParameter('cid', 'i', $cid);
        $erg = $this->db->pselect1();
        if (!$erg['id']) {
            $this->db->setStatement("INSERT INTO t:lw_plugins (plugin, container_id) VALUES (:plugin, :cid)");
            $this->db->bindParameter('plugin', 's', $plugin);
            $this->db->bindParameter('cid', 'i', $cid);
            $ok = $this->db->pdbquery();
        }
        if ($erg['parameter']) {
            $data['parameter'] = unserialize(stripslashes($erg['parameter']));
        } else {
            $data['parameter'] = array();
        }
        $data['content'] = stripslashes($erg['content']);
        $data['item_id'] = intval($erg['item_id']);
        return $data;
    }

    public function savePluginData($plugin, $cid, $parameter=false, $content=false, $item_id=false)
    {
        $this->db->setStatement("UPDATE t:lw_plugins set parameter = :parameter, content = :content, item_id = :item_id WHERE plugin = :plugin AND container_id = :cid");
        $this->db->bindParameter('parameter', 's', addslashes(serialize($parameter)));
        $this->db->bindParameter('content', 's', addslashes($content));
        $this->db->bindParameter('item_id', 'i', $item_id);
        $this->db->bindParameter('plugin', 's', $plugin);
        $this->db->bindParameter('cid', 'i', $cid);
        $ok = $this->db->pdbquery();
        return $ok;
    }    
}
