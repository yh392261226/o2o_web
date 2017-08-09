<!DOCTYPE html>
<html>
<head>
    <title>添加管理员</title>
</head>
<body>
    <form method="post" action="insert">
    <label> 用户名：<input type="text" name="manager_name"/></label>
    <label> 密码：<input type="password" name="password"/></label>
    <label> 确认密码：<input type="password" name="pwd_confirm"/></label>
    <tr>
       <td class="label">角色选择</td>
        <td>
            <select name="select_role">
                <option value="">请选择...</option>
                {{foreach from=$role_list item=list}}
                    <option value="{{$list.mpg_id}}" >{{$list.mpg_name}}</option>
                {{/foreach}}
            </select>
        </td>
    </tr>
    <input type="submit" value="提交" />
</form>
</body>
</html>