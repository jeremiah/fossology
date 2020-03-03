<?php
/***********************************************************
 * Copyright (C) 2019-2020, Siemens AG
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 ***********************************************************/

namespace Fossology\Atarashi\Ui;

use Fossology\Lib\Plugin\AgentPlugin;

class AtarashiAgentPlugin extends AgentPlugin
{
  public function __construct() {
    $this->Name = "agent_atarashi";
    $this->Title =  _("atarashi License Analysis");
    $this->AgentName = "atarashi";

    parent::__construct();
  }

  function AgentHasResults($uploadId=0)
  {
    return CheckARS($uploadId, $this->AgentName, "atarashi agent", "atarashi_ars");
  }

  function preInstall()
  {
    if ($this->isAtarashiInstalled()) {
      menu_insert("Agents::" . $this->Title, 0, $this->Name);
    }
  }

  public function isAtarashiInstalled()
  {
    exec('which atarashi', $lines, $returnVar);
    return (0==$returnVar);
  }
}

register_plugin(new AtarashiAgentPlugin());