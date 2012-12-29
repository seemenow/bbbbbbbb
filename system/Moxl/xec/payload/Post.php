<?php
/*
 * @file Post.php
 * 
 * @brief Handle incoming Post (XEP 0277 Microblog)
 * 
 * Copyright 2012 edhelas <edhelas@edhelas-laptop>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */

namespace moxl;

class Post extends XECPayload
{
    public function handle($stanza, $parent = false) {     
        if($stanza->item) {
            $u = new \User();
            $to = $u->getLogin();
            
            $from = substr((string)$stanza->item->entry->source->author->uri, 5);
            
            if(isset($to) && isset($from)) {
            
                $p = new \Post();

                $query = \Post::query()->select()
                                           ->where(array(
                                                   'key' => $to,
                                                   'jid' => $from,
                                                   'nodeid' => (string)$stanza->item->attributes()->id));
                $data = \Post::run_query($query);

                if($data) {
                    $p = $data[0];
                }

                $p->setPost($stanza->item, $from);
                
                $p->run_query($p->query()->save($p));
                
                $evt = new \Event();
                $evt->runEvent('post');
            }
        }
    }
}
