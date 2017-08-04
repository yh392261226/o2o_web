<?php /* Smarty version 2.6.16, created on 2017-08-02 16:49:01
         compiled from manager/edit_manager_info.html */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>修改会员信息</title>
</head>
<body>
	<form action="/manager/edit_manager_info" method="post" accept-charset="utf-8"><br>
		管理员名称：<input type="text" name="m_name" id="" value="<?php echo $this->_tpl_vars['manager']['m_name']; ?>
"><br>
		管理员密码：<input type="password" name="m_pass" id="" value="<?php echo $this->_tpl_vars['manager']['m_pass']; ?>
"><br>
		请确认密码：<input type="password" name="m_pass_check" id="" value="<?php echo $this->_tpl_vars['manager']['m_pass']; ?>
"><br>
		状态：<input type="text" name="m_status" id="" value="<?php echo $this->_tpl_vars['manager']['m_status']; ?>
"> //-2 离职管理员/删除 -1禁止登陆 默认0正常 1限制类管理员 9总管理员<br>
		管理权限组：
		<select name="mpg_id" id="">
			<option value="">请选择权限组</option>}
			option
			<?php $_from = $this->_tpl_vars['mpg_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['val']):
?>
				<option value="<?php echo $this->_tpl_vars['manager']['mpg_id']; ?>
" <?php if ($this->_tpl_vars['val']['mpg_id'] == $this->_tpl_vars['manager']['mpg_id']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['val']['mpg_name']; ?>
</option>
			<?php endforeach; endif; unset($_from); ?>
		</select><br>
		<input type="submit" value="提交">
	</form>
</body>
</html>