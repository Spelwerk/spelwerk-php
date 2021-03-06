<?php global $form, $component, $user, $sitemap;

$component->title('Reset Password');

$component->wrapStart();
$form->form([
    'special' => 'user',
    'do' => 'password',
    'return' => 'user'
]);

$form->email(true,'email','Email','We will send you an email with a secure Verification Code that you will use to reset your password.');
$form->submit(false,'Reset');
$component->wrapEnd();
?>