<?php

    //  php scripts/import_project_from_yml.php -i -u 1 -f /home/explosioncorp/public_ftp/incoming/index.yml


    define("APPLICATION_PATH", __DIR__ . "/../");
    date_default_timezone_set('America/New_York');

    require_once APPLICATION_PATH . 'vendor/autoload.php';
    require_once APPLICATION_PATH . 'src/library/Data/Base.php';
    require_once APPLICATION_PATH . 'src/library/Logic/Projects.php';
    use Colors\Color;
    use Commando\Command;
    use Symfony\Component\Yaml\Yaml;

    $c = new Color();
    $configs = Yaml::parse(
        file_get_contents(APPLICATION_PATH . "configs/configs.yml"));
    $db = new DataBase(
        $configs['mysql']['host'],
        $configs['mysql']['database'],
        $configs['mysql']['user'],
        $configs['mysql']['password']);

    $cmd = new Command();
    $cmd->beepOnError();
    $cmd->flag('u')
        ->require(true)
        ->aka('user_id')
        ->describedAs('Id for the user from users table.');
    $cmd->flag('f')
        ->require(true)
        ->aka('file')
        ->describedAs('Location of the yaml file.');



    echo $c(
"   ___       _ _
/ _ \_   _| | |
/ /_)/ | | | | |
/ ___/| |_| | | |
\/     \__,_|_|_|
             "
        )
        ->white()->bold()->highlight('blue') . PHP_EOL;

    echo $c("importing yml as project.")
        ->yellow()->bold() . PHP_EOL;

    $user = $db->fetchOne(
        $configs['sql']['users']['get_by_id'],[
            'id' => $cmd['u']]);

    $projectYml = Yaml::parse(
        file_get_contents($cmd['f']));

    $projectService = new Projects($db, $configs, (object) $user);

    $project = $projectService->create($projectYml);

    if(is_array($projectYml['storyboards'])){
        foreach ($projectYml['storyboards'] as $_storyboard) {
            $_storyboard['project_id'] = $project['id'];
            $storyboard = $projectService->createProjectStoryboard($_storyboard);
            array_reverse($_storyboard['boards']);
            foreach($_storyboard['boards'] as $i=>$_board){
                $_board['name'] = $_board['id'];
                $_board['description'] = $_board['notes'];
                $_board['storyboard_id'] = $storyboard['id'];
                $panel = $projectService->createProjectStoryboardPanel($_board, $i);

                array_reverse($_board['images']);
                foreach ($_board['images'] as $_revision) {
                    $_revision['content'] = $_revision['display'];
                    $_revision['panel_id'] = $panel['id'];
                    $revision = $projectService->createProjectStoryboardPanelRevision($_revision);
                }
                if(is_array($_board['comments'])){
                    foreach ($_board['comments'] as $_comment) {

                        $_user = $db->fetchOne(
                            $configs['sql']['users']['get_by_username'],
                            [
                                'username' => $_comment['user']
                            ]
                        );

                        $_comment['panel_id'] = $panel['id'];
                        $_comment['user_id'] = $_user['id'];
                        $_comment['status'] = $_comment['status'] ? $_comment['status'] : 'new';
                        $_comment['comment'] = iconv('UTF-8', 'ASCII//TRANSLIT', $_comment['comment']);
                        $_comment['date_added'] = date("Y-m-d h:i:s", $_comment['date']);
                        $comment = $projectService->createProjectStoryboardPanelComment($_comment);
                    }
                }
            }
        }
    }

    if (is_array($projectYml['characters']['list'])) {
        foreach ($projectYml['characters']['list'] as $idx=>$_character) {
            $_character['project_id'] = $project['id'];
            $character = $projectService->createProjectCharacter($_character, $idx);
            if (is_array($_character['images'])) {
                foreach ($_character['images'] as $_revision){
                    $_revision['content'] = $_revision['display'];
                    $_revision['character_id'] = $character['id'];
                    $revision = $projectService->createProjectCharacterRevision($_revision);
                }
            }
        }
    }

    if (is_array($projectYml['concept_art']['list'])) {
        foreach ($projectYml['concept_art']['list'] as $idx=>$_concept_art) {
            $_concept_art['project_id'] = $project['id'];
            $concept_art = $projectService->createProjectConceptArt($_concept_art, $idx);
            if (is_array($_concept_art['images'])) {
                foreach ($_concept_art['images'] as $_revision){
                    $_revision['content'] = $_revision['display'];
                    $_revision['concept_art_id'] = $concept_art['id'];
                    $revision = $projectService->createProjectConceptArtRevision($_revision);
                }
            }
        }
    }

    if (is_array($projectYml['reference_images']['list'])) {
        foreach ($projectYml['reference_images']['list'] as $idx=>$_img) {
            $_img['project_id'] = $project['id'];
            $_img['name'] = $_img['id'];
            $_img['description'] = $_img['note'];
            $_img['content'] = $_img['url'];
            $img = $projectService->createProjectReferenceImage($_img, $idx);
        }
    }

    if (is_array($projectYml['locations']['list'])) {
        foreach ($projectYml['locations']['list'] as $idx=>$_loc) {
            $_loc['project_id'] = $project['id'];
            $img = $projectService->createProjectLocation($_loc, $idx);
        }
    }

    echo $c("Created project ".$project['id'].".")
        ->yellow()->bold() . PHP_EOL;
