<?php echo $this->fetch('header.html'); ?>

<p><?php echo $this->_var['__APP__']; ?></p>
<p><?php echo $this->_var['Controller']; ?></p>
<p><?php echo $this->_var['Action']; ?></p>
<p><?php echo $this->_var['TMPL']; ?></p>
<p><?php 
$k = array (
  'name' => 'getTime',
);
echo $k['name']();
?></p>
<p><?php
echo lang("getTime"); 
?></p>
<p><?php
echo conf("app","appName"); 
?></p>
<p><?php
echo conf("app","appName"); 
?></p>

<ul>
<?php $_from = $this->_var['data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
    <?php if ($this->_var['key'] == 'Alan'): ?>
    <li><b><?php echo $this->_var['key']; ?> - <?php echo $this->_var['data']; ?></b></li>
    <?php else: ?>
    <li><?php echo $this->_var['key']; ?> - <?php echo $this->_var['data']; ?></li>
    <?php endif; ?>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>

<div>
    <img src="/verify" onclick="this.src='/verify?'+Math.random()" >
</div>
<form action="/verify" method="post">
    <input type="text" name="verify_code" autocomplete="false">
    <input type="submit" value="submit">
</form>



</body>
</html>