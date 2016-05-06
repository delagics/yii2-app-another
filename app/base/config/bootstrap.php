<?php
Yii::setAlias('@base', dirname(__DIR__));
Yii::setAlias('@front', dirname(dirname(__DIR__)) . '/front');
Yii::setAlias('@back', dirname(dirname(__DIR__)) . '/back');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@storagePath', dirname(dirname(dirname(__DIR__))) . '/public/storage');
Yii::setAlias('@storageUrl', '/storage');
