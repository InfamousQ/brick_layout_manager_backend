<?php
    /** @var \Hybridauth\User\Profile $profile */
    /** @var int $user_id */
    /** @var \League\Plates\Template\Template $this */
    $this->layout('layout::layout_user', ['title' => 'Login', 'profile' => $profile, 'user_id' => $user_id]);
    $this->start('login');
?>
<div>
    <h2>Social networks</h2>
    <table id="authenticate-social-plugins" data-authentication-url="api/v1/user/authenticate">
        <tr>
            <td><a class="authentication-social-plugin-link" href="#" data-social-plugin="Facebook"><img alt="Login with Facebook" src="https://cdnjs.cloudflare.com/ajax/libs/webicons/2.0.0/webicons/webicon-facebook.png"></a></td>
            <td><a class="authentication-social-plugin-link" href="#" data-social-plugin="Github"><img alt="Login with Github" src="https://cdnjs.cloudflare.com/ajax/libs/webicons/2.0.0/webicons/webicon-github.png" /></a></td>
        </tr>
    </table>
</div>
<?php
    $this->stop();
    $this->start('profile');
?>
<div>
    <p><b>Hello, <?= $this->e($profile->displayName) ?>!</b></p>
    <p>Your email is <?= $this->e($profile->email) ?></p>
    <p>Your LManager id is <?= $user_id ?></p>
    <p><a href="user/logout">Logout</a></p>
</div>

<?php $this->stop(); ?>


