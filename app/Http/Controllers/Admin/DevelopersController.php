<?php

namespace App\Http\Controllers\Admin;

use Hashids;
use Illuminate\Http\Request;

class DevelopersController extends AdminController
{
    
    public function beanstalkdConsole()
    {
        require_once base_path('vendor/ptrofimov/beanstalk_console/lib/include.php');
        
        $GLOBALS['config'] = [
            /**
             * List of servers available for all users
             */
            'servers' => [
                'Local Beanstalkd' => 'beanstalk://localhost:11300'
            ],
            /**
             * Saved samples jobs are kept in this file, must be writable
             */
            'storage' => base_path('storage/beanstalkd_console.json'),
            /**
             * Optional Basic Authentication
             */
            'auth' => array(
                'enabled' => false,
                'username' => 'admin',
                'password' => 'password',
            ),
            /**
             * Version number
             */
            'version' => '1.7.5',
        ];
        
        $console = new \Console;
        $errors = $console->getErrors();
        $fields = $console->getTubeStatFields();
        $groups = $console->getTubeStatGroups();
        $visible = $console->getTubeStatVisible();
        $tplVars = $console->getTplVars();
        extract($tplVars);
        
        return view('admin.pages.developers.beanstalkd-console', array_merge($tplVars, [
            'title' => 'Beanstalkd Console',
            'tplBase' => base_path('vendor/ptrofimov/beanstalk_console/lib/tpl/'),
            'console' => $console,
            'consoleErrors' => $errors,
            'fields' => $fields,
            'groups' => $groups,
            'visible' => $visible,
            'tplVars' => $tplVars,
            'servers' => $console->getServers()
        ]));
    }
    
    public function hashidsEncoder(Request $request)
    {
        $action = $request->get('action');
        $decodeHash = $request->get('decode_hash');
        $encodeHash = $request->get('encode_hash');
        
        if ($encodeHash) {
            $encoded = Hashids::encode($encodeHash);
            
            if (!empty($encoded)) {
                flash()->success(trans('labels.encoded').': <code>'.$encoded.'</code>');
            }
            else {
                flash()->error(trans('messages.encoding_error'));
            }
        }
        else {
            $decoded = Hashids::decode($decodeHash);
            
            if (!empty($decoded)) {
                $decoded = (int)$decoded[0];
                flash()->success(trans('labels.decoded').': <code>'.$decoded.'</code>');
            }
            else {
                flash()->error(trans('messages.decoding_error'));
            }
        }
        
        return view('admin.pages.developers.hashids', [
            'title' => trans('labels.hashids_encoder'),
            'decode_hash' => $decodeHash,
            'encode_hash' => $encodeHash
        ]);
    }
}
