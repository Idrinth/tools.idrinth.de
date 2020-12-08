<?php
$classes=array(
    array('Unknown','Unknown'),
    array('Witch Hunter','Empire'),
    array('Bright Wizard','Empire'),
    array('Warriorpriest','Empire'),
    array('Knight of the Blazing Sun','Empire'),
    array('Slayer','Dwarfs'),
    array('Engineer','Dwarfs'),
    array('Runepriest','Dwarfs'),
    array('Ironbreaker','Dwarfs'),
    array('White Lion','Asur'),
    array('Shadow Warrior','Asur'),
    array('Archmage','Asur'),
    array('Swordmaster','Asur'),
    array('Marauder','Chaos'),
    array('Magus','Chaos'),
    array('Zealot','Chaos'),
    array('Chosen','Chaos'),
    array('Choppa','Orcs'),
    array('Squig Herder','Goblins'),
    array('Shaman','Goblins'),
    array('Black Orc','Orcs'),
    array('Witch Elf','Druchii'),
    array('Sorcerer','Druchii'),
    array('Diciple of Khaine','Druchii'),
    array('Black Guard','Druchii'),
);
$cn2id = array(
    'Unknown' =>0,
    'Witch Hunter' => 1,
    'Bright Wizard' => 2,
    'Warrior Priest' => 3,
    'Knight of the Blazing Sun' => 4,
    'Slayer' => 5,
    'Engineer' => 6,
    'Rune Priest' => 7,
    'Ironbreaker' => 8,
    'White Lion' => 9,
    'Shadow Warrior' => 10,
    'Archmage' => 11,
    'Swordmaster' => 12,
    'Marauder' => 13,
    'Magus' => 14,
    'Zealot' => 15,
    'Chosen' => 16,
    'Choppa' => 17,
    'Squig Herder' => 18,
    'Shaman' => 19,
    'Black Orc' => 20,
    'Witch Elf' => 21,
    'Sorcerer' => 22,
    'Disciple of Khaine' => 23,
    'Black Guard' => 24,
);
$fonts = scandir($_SERVER['DOCUMENT_ROOT'].'/ressources/fonts');
unset($fonts[0]);
unset($fonts[1]);
sort($fonts);
# $db = new mysqli('localhost','db10636098-addon','AddonServer','db10636098-addons');
$db = new mysqli('localhost','tools','9177ed7dd05b28dd4b6471f804a2f0e0','tools');
$db->set_charset('utf8');