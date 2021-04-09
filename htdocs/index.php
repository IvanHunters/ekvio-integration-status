<?php

$redis = new Redis();
$redis->connect(
    'redis',
    6379
);
$redis->auth($_ENV['REDIS_PASSWORD']);

$statsJson = $redis->get('statistics');
$statsArray = json_decode($statsJson, true);
$groupsMethods = array_keys($statsArray);
$titles = explode(",", getenv('INTEGRATION_ENV_TITLES'));
?>
<table border="1">
    <thead>
    <th>METHODS CATEGORY</th>
    <?php foreach ($titles as $title): ?>
    <th><?=$title?></th>
    <?php endforeach; ?>
    </thead>
    <tbody>
    <?php foreach ($groupsMethods as $group): ?>
        <tr>
            <?php $stats = $statsArray[$group]; ?>
            <td><?=mb_strtoupper($group)?>:</td>
            <?php foreach ($titles as $title): ?>
                <td><?=$stats['data'][$title]['generalStatus']?></td>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($statsArray[$group]['methods'] as $method):?>
            <tr>
                <td>&nbsp;--<?=$method?></td>
                <?php foreach ($titles as $title): ?>
                    <?php $status = $stats['data'][$title]['statusMethods'][$method]; ?>
                        <td><?=is_array($status)? $status['message'] : $status?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </tbody>
</table>
