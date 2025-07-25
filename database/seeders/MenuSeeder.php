<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $menus = '[
            {"parent_id":null,"title":"Home","subtitle":"Home","code":"home","url":"home","model":null,"icon":"fas fa-home","type":"backend","show":"1","active":"1","sort":"0","children":[]},
            {"parent_id":null,"title":"Menu","subtitle":"Menu","code":"menu","url":"menu","model":"Menu","icon":"fas fa-bars","type":"backend","show":"1","active":"1","sort":"1","children":[]},
            {"parent_id":null,"title":"User","subtitle":"User","code":"users","url":"users","model":"User","icon":"fas fa-user","type":"backend","show":"1","active":"1","sort":"2","children":[]},
            {"parent_id":null,"title":"Role","subtitle":"Role","code":"roles","url":"roles","model":"Role","icon":"fas fa-universal-access","type":"backend","show":"1","active":"1","sort":"3","children":[]}
            ]';
        $data = json_decode($menus, true);
        foreach ($data as $item) {
            if ($menu = \App\Models\Menu::updateOrCreate(collect($item)->except('children')->toArray())) {
                if(count($item['children']) > 0) {
                    $this->menuChildren($item['children'], $menu->id);
                }
            }
        }
    }

    private function menuChildren($children, $id)
    {
        foreach ($children as $item) {
            if ($menu = \App\Models\Menu::updateOrCreate(collect($item)->except('children')->toArray())) {
                $menu->update(['parent_id'=>$id]);
                if(count($item['children']) > 0) {
                    $this->menuChildren($item['children'], $menu->id);
                }
            }
        }
    }
}
