<?php
    /** @var \League\Plates\Template\Template $this */
    /** @var \Hybridauth\User\Profile $profile */
?>
<!DOCTYPE HTML>
<html>
<head>
    <title><?= $this->e($title) ?></title>
</head>
<body>
<?php
    if (null == $profile) :
?>
<?= $this->section('login') ?>
<?php
    else :
?>
<?= $this->section('profile') ?>
<?php
    endif;
?>
</body>
</html>