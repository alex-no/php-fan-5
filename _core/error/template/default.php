<?php
return array(
    0      => $this->setResponseHeader(200) . $this->setContentType('text'),
    'text' => $this->convArrayToSting($this->getTplVar()),
);
?>
