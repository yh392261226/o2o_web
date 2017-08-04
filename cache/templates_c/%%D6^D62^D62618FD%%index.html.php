<?php /* Smarty version 2.6.16, created on 2017-08-03 09:53:57
         compiled from manager/index.html */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>后台管理员</title>
</head>
<body>
<?php $_from = $this->_tpl_vars['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['val']):
?>
	<?php if ($this->_tpl_vars['val']['m_id'] == 1): ?>
		<?php echo $this->_tpl_vars['val']['m_name']; ?>

	<?php else: ?>
		<?php echo $this->_tpl_vars['val']['m_pass']; ?>

	<?php endif;  endforeach; endif; unset($_from);  echo $this->_tpl_vars['page']; ?>

</body>
</html>