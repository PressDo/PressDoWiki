<?php

use Latte\Runtime as LR;

/** source: views/layouts/acl.latte */
final class Templatec707c1b131 extends Latte\Runtime\Template
{

	public function main(): array
	{
		extract($this->params);
		echo '<form method="POST" id="rf" action="';
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($_SERVER['REQUEST_URI'])) /* line 1 */;
		echo '">
    <input type="hidden" id="rft" name="target" value>
</form>
<div class="wiki-content">
';
		$acls = ['doc' => $wiki['page']['data']['docACL']['acls'], 'ns' => $wiki['page']['data']['nsACL']['acls']] /* line 5 */;
		$types = ['doc', 'ns'] /* line 6 */;
		$iterations = 0;
		foreach ($types as $a) /* line 7 */ {
			echo '    <div data-pressdo-acl-partarea>
        <h2 data-pressdo-acl-title>';
			echo LR\Filters::escapeHtmlText($lang['acl'][$a.'acl']) /* line 8 */;
			echo '</h2>
';
			$iterations = 0;
			foreach ($wiki['page']['data']['ACLTypes'] as $A) /* line 9 */ {
				echo '            <h4 data-pressdo-acl-type>';
				echo LR\Filters::escapeHtmlText($lang['acl'][$A]) /* line 10 */;
				echo '</h4>
            <div data-pressdo-acl-part>
                <table data-pressdo-acl-part>
                    <colgroup data-pressdo-acl-part>
                        <col data-pressdo-acl-part style="width:60px;">
                        <col data-pressdo-acl-part>
                        <col data-pressdo-acl-part style="width:80px;">
                        <col data-pressdo-acl-part style="width:200px;">
                        <col data-pressdo-acl-part style="width:60px;">
                    </colgroup>
                    <thead data-pressdo-acl-part>
                        <tr data-pressdo-acl-part>
                            <th data-pressdo-acl-part>No</th>
                            <th data-pressdo-acl-part>Condition</th>
                            <th data-pressdo-acl-part>Action</th>
                            <th data-pressdo-acl-part>Expiration</th>
                            <th data-pressdo-acl-part></th>
                        </tr>
                    </thead>
                    <tbody data-pressdo-acl-part>
';
				if (!$acls[$a][$A][0]) /* line 30 */ {
					echo '                        <tr data-pressdo-acl-part><td colspan="5" data-pressdo-acl-part>(';
					echo LR\Filters::escapeHtmlText($lang['msg']['empty_set_'.$a.'acl']) /* line 30 */;
					echo ')</td></tr>
';
				}
				$aclcnt = count($acls[$a][$A]) /* line 31 */;
				for ($i=0;
				$i<$aclcnt;
				++$i) /* line 32 */ {
					$acls[$a][$A][$i]['expired'] = ($acls[$a][$A][$i]['expired'] === 0)? $lang['acl']['forever'] : date("Y-m-d H:i:s",$acls[$a][$A][$i]['expired']) /* line 33 */;
					echo '                            <tr data-pressdo-acl-part><td data-pressdo-acl-part>
                                ';
					echo LR\Filters::escapeHtmlText($i+1) /* line 35 */;
					echo '</td><td data-pressdo-acl-part>
                                ';
					echo LR\Filters::escapeHtmlText($acls[$a][$A][$i]['condition']) /* line 36 */;
					echo '</td><td data-pressdo-acl-part>
                                ';
					echo LR\Filters::escapeHtmlText($lang['acl'][$acls[$a][$A][$i]['action']]) /* line 37 */;
					echo '</td><td data-pressdo-acl-part>
                                ';
					echo LR\Filters::escapeHtmlText($acls[$a][$A][$i]['expired']) /* line 38 */;
					echo '</td><td data-pressdo-acl-part>
';
					if ($allow[$a] === true) /* line 39 */ {
						echo '                                <button delete-info="';
						echo LR\Filters::escapeHtmlAttr($a) /* line 39 */;
						echo '.';
						echo LR\Filters::escapeHtmlAttr($acls[$a][$A][$i]['id']) /* line 39 */;
						echo '" msg="';
						echo LR\Filters::escapeHtmlAttr($lang['msg']['acl_delete_confirm']) /* line 39 */;
						echo '" type="button" class="btn btn-red d">';
						echo LR\Filters::escapeHtmlText($lang['acl']['delete']) /* line 39 */;
						echo '</button>';
					}
					echo '</td>
                            </tr>
';
				}
				echo '                    </tbody>
                </table>
            </div>
';
				if ($allow[$a] === true) /* line 45 */ {
					echo '            <form data-pressdo-acl-change method="post">
                <input type="hidden" name="acl_target" value="';
					echo LR\Filters::escapeHtmlAttr($a.'.'.$A) /* line 46 */;
					echo '">
                <div data-pressdo-acl-change class="g">
                    <label data-pressdo-acl-change>Condition</label> 
                    <div data-pressdo-acl-change>
                        <select id="cs" i="';
					echo LR\Filters::escapeHtmlAttr($a.'-'.$A) /* line 50 */;
					echo '" name="target_type" data-pressdo-acl-change>
                            <option data-pressdo-acl-change value="perm">';
					echo LR\Filters::escapeHtmlText($lang['perm']) /* line 51 */;
					echo '</option> 
                            <option data-pressdo-acl-change value="member">';
					echo LR\Filters::escapeHtmlText($lang['member']) /* line 52 */;
					echo '</option> 
                            <option data-pressdo-acl-change value="ip">';
					echo LR\Filters::escapeHtmlText($lang['ip']) /* line 53 */;
					echo '</option> 
                            <option data-pressdo-acl-change value="geoip">';
					echo LR\Filters::escapeHtmlText($lang['geoip']) /* line 54 */;
					echo '</option> 
                            <option data-pressdo-acl-change value="aclgroup">';
					echo LR\Filters::escapeHtmlText($lang['aclgroup']) /* line 55 */;
					echo '</option>
                        </select> 
                        <select id="ct" i="';
					echo LR\Filters::escapeHtmlAttr($a.'-'.$A) /* line 57 */;
					echo '" name="target_name" v="inilne-block" data-pressdo-acl-change>
                            <option data-pressdo-acl-change value="any">';
					echo LR\Filters::escapeHtmlText($lang['perm']['any']) /* line 58 */;
					echo '</option> 
                            <option data-pressdo-acl-change value="member">';
					echo LR\Filters::escapeHtmlText($lang['perm']['member']) /* line 59 */;
					echo '</option> 
                            <option data-pressdo-acl-change value="admin">';
					echo LR\Filters::escapeHtmlText($lang['perm']['admin']) /* line 60 */;
					echo '</option> 
                            <option data-pressdo-acl-change value="member_signup_15days_ago">';
					echo LR\Filters::escapeHtmlText($lang['perm']['member_singup_15days_ago']) /* line 61 */;
					echo '</option> 
                            <option data-pressdo-acl-change value="document_contributor">';
					echo LR\Filters::escapeHtmlText($lang['perm']['document_contributor']) /* line 62 */;
					echo '</option> 
                            <option data-pressdo-acl-change value="contributor">';
					echo LR\Filters::escapeHtmlText($lang['perm']['contributor']) /* line 63 */;
					echo '</option> 
                            <option data-pressdo-acl-change value="match_username_and_document_title">';
					echo LR\Filters::escapeHtmlText($lang['perm']['match_username_and_document_title']) /* line 64 */;
					echo '</option>
                        </select>
                        <input id="ct_r" i="';
					echo LR\Filters::escapeHtmlAttr($a.'-'.$A) /* line 66 */;
					echo '" name="" type="hidden" data-pressdo-acl-span data-pressdo-acl-change>
                    </div>
                </div> 
                <div data-pressdo-acl-change class="g">
                    <label data-pressdo-acl-change>Action :</label> 
                    <div data-pressdo-acl-change>
                        <select name="action" data-pressdo-acl-change>
                            <option data-pressdo-acl-change value="allow">';
					echo LR\Filters::escapeHtmlText($lang['acl']['allow']) /* line 73 */;
					echo '</option> 
                            <option data-pressdo-acl-change value="deny">';
					echo LR\Filters::escapeHtmlText($lang['acl']['deny']) /* line 74 */;
					echo '</option> 
                            <option data-pressdo-acl-change value="gotons">';
					echo LR\Filters::escapeHtmlText($lang['acl']['gotons']) /* line 75 */;
					echo '</option>
                        </select>
                    </div>
                </div> 
                <div data-pressdo-acl-change class="g">
                    <label data-pressdo-acl-change>Duration :</label> 
                    <div data-pressdo-acl-change>
                        <span data-pressdo-acl-span data-pressdo-acl-change>
                            <input id="dr" name="duration_raw" data-pressdo-acl-span type="hidden" name="0" value="0" i="';
					echo LR\Filters::escapeHtmlAttr($a.'-'.$A) /* line 83 */;
					echo '">
                            <select id="duration" data-pressdo-acl-span i="';
					echo LR\Filters::escapeHtmlAttr($a.'-'.$A) /* line 84 */;
					echo '">
                                <option data-pressdo-acl-span value="0">';
					echo LR\Filters::escapeHtmlText($lang['acl']['forever']) /* line 85 */;
					echo '</option>
                                <option data-pressdo-acl-span value="300">';
					echo LR\Filters::escapeHtmlText($lang['acl']['300s']) /* line 86 */;
					echo '</option>
                                <option data-pressdo-acl-span value="600">';
					echo LR\Filters::escapeHtmlText($lang['acl']['600s']) /* line 87 */;
					echo '</option>
                                <option data-pressdo-acl-span value="1800">';
					echo LR\Filters::escapeHtmlText($lang['acl']['1800s']) /* line 88 */;
					echo '</option>
                                <option data-pressdo-acl-span value="3600">';
					echo LR\Filters::escapeHtmlText($lang['acl']['3600s']) /* line 89 */;
					echo '</option>
                                <option data-pressdo-acl-span value="7200">';
					echo LR\Filters::escapeHtmlText($lang['acl']['7200s']) /* line 90 */;
					echo '</option>
                                <option data-pressdo-acl-span value="86400">';
					echo LR\Filters::escapeHtmlText($lang['acl']['86400s']) /* line 91 */;
					echo '</option>
                                <option data-pressdo-acl-span value="259200">';
					echo LR\Filters::escapeHtmlText($lang['acl']['259200s']) /* line 92 */;
					echo '</option>
                                <option data-pressdo-acl-span value="432000">';
					echo LR\Filters::escapeHtmlText($lang['acl']['432000s']) /* line 93 */;
					echo '</option>
                                <option data-pressdo-acl-span value="604800">';
					echo LR\Filters::escapeHtmlText($lang['acl']['604800s']) /* line 94 */;
					echo '</option>
                                <option data-pressdo-acl-span value="1209600">';
					echo LR\Filters::escapeHtmlText($lang['acl']['1209600s']) /* line 95 */;
					echo '</option>
                                <option data-pressdo-acl-span value="1814400">';
					echo LR\Filters::escapeHtmlText($lang['acl']['1814400s']) /* line 96 */;
					echo '</option>
                                <option data-pressdo-acl-span value="2419200">';
					echo LR\Filters::escapeHtmlText($lang['acl']['2419200s']) /* line 97 */;
					echo '</option>
                                <option data-pressdo-acl-span value="4838400">';
					echo LR\Filters::escapeHtmlText($lang['acl']['4838400s']) /* line 98 */;
					echo '</option>
                                <option data-pressdo-acl-span value="7257600">';
					echo LR\Filters::escapeHtmlText($lang['acl']['7257600s']) /* line 99 */;
					echo '</option>
                                <option data-pressdo-acl-span value="14515200">';
					echo LR\Filters::escapeHtmlText($lang['acl']['14515200s']) /* line 100 */;
					echo '</option>
                                <option data-pressdo-acl-span value="29030400">';
					echo LR\Filters::escapeHtmlText($lang['acl']['29030400s']) /* line 101 */;
					echo '</option> 
                                <option data-pressdo-acl-span value="raw">';
					echo LR\Filters::escapeHtmlText($lang['acl']['raw']) /* line 102 */;
					echo '</option>
                            </select>
                            <input id="dr_e" data-pressdo-acl-span type="hidden" name="0" value="0" i="';
					echo LR\Filters::escapeHtmlAttr($a.'-'.$A) /* line 104 */;
					echo '">
                            <select id="du" data-pressdo-acl-span v="none" i="';
					echo LR\Filters::escapeHtmlAttr($a.'-'.$A) /* line 105 */;
					echo '">
                                <option data-pressdo-acl-span value="1">';
					echo LR\Filters::escapeHtmlText($lang['acl']['second']) /* line 106 */;
					echo '</option>
                                <option data-pressdo-acl-span value="60">';
					echo LR\Filters::escapeHtmlText($lang['acl']['minute']) /* line 107 */;
					echo '</option>
                                <option data-pressdo-acl-span value="3600">';
					echo LR\Filters::escapeHtmlText($lang['acl']['hour']) /* line 108 */;
					echo '</option>
                                <option data-pressdo-acl-span value="86400">';
					echo LR\Filters::escapeHtmlText($lang['acl']['day']) /* line 109 */;
					echo '</option>
                                <option data-pressdo-acl-span value="604800">';
					echo LR\Filters::escapeHtmlText($lang['acl']['week']) /* line 110 */;
					echo '</option>
                            </select>
                        </span>
                    </div>
                </div>
                <button type="submit" class="s btn btn-blue">';
					echo LR\Filters::escapeHtmlText($lang['acl']['add']) /* line 115 */;
					echo '</button>
            </form>
';
				}
				$iterations++;
			}
			echo '    </div>
';
			$iterations++;
		}
		echo '</div>';
		return get_defined_vars();
	}


	public function prepare(): void
	{
		extract($this->params);
		if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
			foreach (array_intersect_key(['A' => '9', 'a' => '7'], $this->params) as $ʟ_v => $ʟ_l) {
				trigger_error("Variable \$$ʟ_v overwritten in foreach on line $ʟ_l");
			}
		}
		
	}

}
