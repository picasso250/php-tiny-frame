<?php
!defined('IN_PTF') && exit('ILLEGAL EXECUTION');
?>
<ul class="msg">
    <?php foreach ($msgs as $msg): ?>
    <li class="msg">
        <span class="name"><?= h($msg->name) ?></span>
        <span class="time"><?= $msg->time ?></span>
        <div><?= nl2br(h($msg->text)) ?></div>
    </li>
    <?php endforeach; ?>
</ul>