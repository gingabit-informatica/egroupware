<p><b>{lang_header}</b>
<hr><p>
{error}

<form action="{form_action}" method="POST">
 {hidden_vars}
 <table border="0" width="35%" align="center">
  <tr>
   <td>{lang_app_name}</td>
   <td><input name="n_app_name" value="{app_name_value}"></td>
  </tr>

  <tr>
   <td>{lang_app_title}</td>
   <td><input name="n_app_title" value="{app_title_value}"></td>
  </tr>

  <tr>
   <td>{lang_status}</td>
   <td>
    <select name="n_app_status">
     {select_status}
    </select>
   </td>
  </tr>

  <tr>
   <td colspan="2" align="center">
    <input type="submit" name="submit" value="{lang_submit_button}">
   </td>
  </tr>

 </table>
</form>
