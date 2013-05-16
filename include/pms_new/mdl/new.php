<?php

/**
 * Copyright (C) 2010-2011 Visman (visman@inbox.ru)
 * Copyright (C) 2008-2010 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

if (!defined('PUN') || !defined('PUN_PMS_NEW'))
	exit;

define('PUN_PMS_LOADED', 1);

define('PUN_ACTIVE_PAGE', 'pms_new');
require PUN_ROOT.'header.php';
?>
<div class="linkst">
	<div class="inbox crumbsplus">
		<ul class="crumbs">
			<li><a href="index.php"><?php echo $lang_common['Index'] ?></a></li>
			<li><span>»&#160;</span><a href="pmsnew.php"><?php echo $lang_pmsn['PM'] ?></a></li>
			<li><span>»&#160;</span><strong><?php echo $lang_pmsn[$pmsn_modul].($sid ? $lang_pmsn['With'].$siduser : '') ?></strong></li>
		</ul>
		<div class="pagepost"></div>
		<div class="clearer"></div>
	</div>
</div>
<?php

generate_pmsn_menu($pmsn_modul);

if ($pmsn_kol_new == 0)
{
?>
	<div class="blockform">
		<p class="postlink actions conr"><?php echo $pmsn_f_cnt ?></p>
		<h2><span><?php echo $lang_pmsn['Info'] ?></span></h2>
		<div class="box">
				<div id="infono" class="inform">
					<fieldset>
						<legend><?php echo $lang_pmsn['Attention'] ?></legend>
						<div class="infldset">
							<p><?php echo $lang_pmsn['Info zero'] ?></p>
						</div>
					</fieldset>
				</div>
		</div>
	</div>
<?php
}
else
{

	// Determine the topic offset (based on $_GET['p'])
	$num_pages = ceil($pmsn_kol_new / $pun_user['disp_topics']);

	$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : intval($_GET['p']);
	$start_from = $pun_user['disp_topics'] * ($p - 1);

	// Generate paging links
	$paging_links = '<span class="pages-label">'.$lang_common['Pages'].' </span>'.paginate($num_pages, $p, 'pmsnew.php?mdl=new'.$sidamp);

  if ($pun_user['g_pm_limit'] != 0 && $pmsn_kol_save >= $pun_user['g_pm_limit'])
		$pmsn_f_savedel = '';
  else
		$pmsn_f_savedel = '<input type="submit" name="save" value="'.$lang_pmsn['Save_'].'">&nbsp;';
	$pmsn_f_savedel .= '<input type="submit" name="delete" value="'.$lang_pmsn['Delete'].'">';

?>
<script language="JavaScript" type="text/JavaScript">
/* <![CDATA[ */
function ChekUncheck()
{
	var i;
	for (i = 0; i < document.posttopic.elements.length; i++)
	{
		if(document.posttopic.chek.checked==true)
		{
			document.posttopic.elements[i].checked = true;
		} else {
			document.posttopic.elements[i].checked = false;
		}
	}
}
/* ]]> */
</script>

	<div class="block">
		<div class="pagepost">
			<p class="pagelink conl"><?php echo $paging_links ?></p>
			<p class="postlink actions conr"><?php echo $pmsn_f_cnt ?></p>
		</div>
		<form method="post" action="pmsnew.php?mdl=newq<?php echo $sidamp ?>" name="posttopic">
		<input type="hidden" name="csrf_hash" value="<?php echo $pmsn_csrf_hash ?>" />
		<input type="hidden" name="p" value="<?php echo $p ?>" />
		<div id="vf" class="blocktable">
			<div class="box">
				<div class="inbox">
					<table cellspacing="0">
					<thead>
						<tr>
							<th class="tcl" scope="col"><?php echo $lang_pmsn['tDialog'] ?></th>
							<th class="tc2" scope="col"><?php echo $lang_pmsn['tStarter'] ?></th>
							<th class="tc2" scope="col"><?php echo $lang_pmsn['tTo'] ?></th>
							<th class="tc3" scope="col"><?php echo $lang_pmsn['tReplies'] ?></th>
							<th class="tc2" scope="col"><?php echo $lang_pmsn['tLast'] ?></th>
							<th scope="col" style="width: 20px;"><input name="chek" type="checkbox" value="" onClick="ChekUncheck()"></th>
						</tr>
					</thead>
					<tbody>
<?php
	$viewt = array_slice($pmsn_arr_new, $start_from, $pun_user['disp_topics']);
	$result = $db->query('SELECT * FROM '.$db->prefix.'pms_new_topics WHERE id IN ('.implode(',', $viewt).') ORDER BY last_posted DESC') or error('Unable to fetch pms topics IDs', __FILE__, __LINE__, $db->error());

	if ($db->num_rows($result))
	{
		$topic_count = 0;
		while ($cur_topic = $db->fetch_assoc($result))
		{
			++$topic_count;
			$status_text = array();
			$item_status = ($topic_count % 2 == 0) ? 'roweven' : 'rowodd';
			$icon_type = 'icon';

			$last_post = format_time($cur_topic['last_posted']).'<br /><span class="byuser">'.$lang_common['by'].' '.pun_htmlspecialchars(($cur_topic['last_poster']) ? $cur_topic['to_user'] : $cur_topic['starter']).'</span>';

			if ($pun_config['o_censoring'] == '1')
				$cur_topic['topic'] = censor_words($cur_topic['topic']);

			if (($cur_topic['starter_id'] == $pun_user['id'] && $cur_topic['topic_to'] > 1) || ($cur_topic['to_id'] == $pun_user['id'] && $cur_topic['topic_st'] > 1))
			{
				$subject = '<a href="pmsnew.php?mdl=topic&amp;tid='.$cur_topic['id'].$sidamp.'">'.pun_htmlspecialchars($cur_topic['topic']).'</a>';
				$status_text[] = '<span class="closedtext">'.$lang_pmsn['Deleted'].'</span>';
				$item_status .= ' iclosed';
			}
			else
				$subject = '<a href="pmsnew.php?mdl=topic&amp;tid='.$cur_topic['id'].$sidamp.'">'.pun_htmlspecialchars($cur_topic['topic']).'</a>';

			if (($cur_topic['starter_id'] == $pun_user['id'] && $cur_topic['topic_st'] == 1) || ($cur_topic['to_id'] == $pun_user['id'] && $cur_topic['topic_to'] == 1))
			{
				$item_status .= ' inew';
				$icon_type = 'icon icon-new';
				$subject = '<strong>'.$subject.'</strong>';
				$subject_new_posts = '<span class="newtext">[ <a href="pmsnew.php?mdl=topic&amp;tid='.$cur_topic['id'].$sidamp.'&amp;action=new" title="'.$lang_common['New posts info'].'">'.$lang_common['New posts'].'</a> ]</span>';
			}
			else
				$subject_new_posts = null;

			// Insert the status text before the subject
			$subject = implode(' ', $status_text).' '.$subject;

			$num_pages_topic = ceil(($cur_topic['replies'] + 1) / $pun_user['disp_posts']);

			if ($num_pages_topic > 1)
				$subject_multipage = '<span class="pagestext">[ '.paginate($num_pages_topic, -1, 'pmsnew.php?mdl=topic&amp;tid='.$cur_topic['id'].$sidamp).' ]</span>';
			else
				$subject_multipage = null;

			// Should we show the "New posts" and/or the multipage links?
			if (!empty($subject_new_posts) || !empty($subject_multipage))
			{
				$subject .= !empty($subject_new_posts) ? ' '.$subject_new_posts : '';
				$subject .= !empty($subject_multipage) ? ' '.$subject_multipage : '';
			}
			
			if ($pun_user['g_view_users'] == '1')
			{
				$user_st = '<a href="profile.php?id='.$cur_topic['starter_id'].'">'.pun_htmlspecialchars($cur_topic['starter']).'</a>';
				$user_to = '<a href="profile.php?id='.$cur_topic['to_id'].'">'.pun_htmlspecialchars($cur_topic['to_user']).'</a>';
			}
			else
			{
				$user_st = pun_htmlspecialchars($cur_topic['starter']);
				$user_to = pun_htmlspecialchars($cur_topic['to_user']);
			}

?>
						<tr class="<?php echo $item_status ?>">
							<td class="tcl">
								<div class="<?php echo $icon_type ?>"><div class="nosize"><?php echo forum_number_format($topic_count + $start_from) ?></div></div>
								<div class="tclcon">
									<div>
										<?php echo $subject."\n" ?>
									</div>
								</div>
							</td>
							<td class="tc2"><?php echo $user_st ?></td>
							<td class="tc2"><?php echo $user_to ?></td>
							<td class="tc3"><?php echo forum_number_format($cur_topic['replies']) ?></td>
							<td class="tc2"><?php echo $last_post ?></td>
							<td style="width: 20px;"><input type="checkbox" name="post_topic[<?php echo $cur_topic['id']?>]" value="1"></td>
						</tr>
<?php
		}
	}
	else
	{
?>
						<tr class="rowodd inone">
							<td class="tcl" colspan="6">
								<div class="icon inone"><div class="nosize"><!-- --></div></div>
								<div class="tclcon">
									<div>
										<strong><?php echo $lang_pmsn['Empty'] ?></strong>
									</div>
								</div>
							</td>
						</tr>
<?php
	}


?>
					</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="pagepost">
			<p class="pagelink conl"><?php echo $paging_links ?></p>
			<p class="postlink conr"><?php echo $pmsn_f_savedel ?></p>
		</div>
		</form>
	</div>
<?php
}