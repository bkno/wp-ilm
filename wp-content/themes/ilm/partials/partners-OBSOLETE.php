<div class="logo-carousel">
    <?
        $partners = [
            'bates-wells-braithwaite' => 'BWB_homepage.jpg',
            'turcan-connell' => 'Turcan-Connell-homepage.png',
            'michelmores' => 'Michelmores-Logo-Blue-homepage.png',
            'legacy-link' => 'legacy_link_homepage.jpg',
            'cleaver-fulton-rankin' => 'Cleaver-Fulton-Rankin.gif',
            'legacy-foresight' => 'LegacyForesight-homepage.png',
            'ward-hadaway' => 'Ward-Hadaway-homepage.png',
            'wilsons' => 'Wilsons_homepage.png',
            'foot-anstey' => 'Foot-Anstey-homepage.png',
            'stone-king-llp' => 'Stone-King-homepage.png',
            'penningtons' => 'Pennington-Manches-homepage.png',
            'clear-firstclass' => 'Clear-Logo-homepage.png',
            'withers' => 'withers-homepage.png',
            'eshkeri-grau' => 'EG-Solicitors.jpg',
            'lester-aldridge' => 'Lester-Aldridge-compact-homepage.png',
            'dreweatts' => 'Dreweatts-homepage.jpg',
            'charles-russell-speechlys' => 'Charles-Russell-homepage.png',
            #'freeths' => 'Freeths_homepage.jpg',            
        ];

        $shuffle = [];        
        foreach($partners as $key => $val) {
            $shuffle[] = [$key => $val];
        }
        shuffle($shuffle);
        foreach ($shuffle as $partner) {
            foreach ($partner as $key => $val) {
                echo '<div class="slide"><a href="/partners/'.$key.'/" style="background-image: url(\'/wp-content/uploads/'.$val.'\');"></a></div>';
            }
        }
    ?>
</div>