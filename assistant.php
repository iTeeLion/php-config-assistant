<?php

// Load configs
$configFilePath = __DIR__ . '/settings.json';
if(!is_file($configFilePath)) die('No config found!');
$arConfig = json_decode(file_get_contents($configFilePath), true);
if(!is_array($arConfig) && count($arConfig) < 1) die('Bad config');
$arPresets = [];

// Parse presets
foreach($arConfig['presets'] as $arPreset){
    foreach($arPreset['directives'] as $directiveName => $directive){
        $patterns[$arPreset['name']][] = '#^;?' . $directiveName . ' = (.*)#m';
        if(isset($directive['disabled']) && $directive['disabled'] == true){
            $raplacements[$arPreset['name']][] = '; ' . $directiveName .' = ' . $directive['value'];
        }else{
            $raplacements[$arPreset['name']][] = $directiveName .' = ' . $directive['value'];
        }
    }
}

// Write configs
foreach($arConfig['files'] as $arFileConfig){
    if(is_file($arFileConfig['path']) && $arFileConfig['preset'] != ''){
        echo 'Write config preset "' . $arFileConfig['preset'] . '" for: ' . $arFileConfig['path'] . PHP_EOL;
        if(!is_file($arFileConfig['path'].'.orig')){
            copy($arFileConfig['path'], $arFileConfig['path'].'.orig');
        }
        copy($arFileConfig['path'], $arFileConfig['path'].'.saved');
    
        $srcConfig = file_get_contents($arFileConfig['path']);
        $resConfig = preg_replace($patterns[$arFileConfig['preset']], $raplacements[$arFileConfig['preset']], $srcConfig);
        file_put_contents($arFileConfig['path'], $resConfig);
    }
}
