<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActionDropdown extends Component
{
    public $model;
    public $show;
    public $actions;
    public $canShow;

    public function __construct($model, $show = null, $actions = [])
    {
        $this->model = $model;
        $this->show = $show ?? ['view', 'edit', 'delete'];
        $this->actions = is_array($actions) ? $actions : [];

        // Filter permission
        $this->canShow = [];
        foreach ($this->show as $action) {
            $this->canShow[$action] = Auth::user()->can($this->getPermission($action));
        }
    }

    private function getPermission(string $action): string
    {
        $prefix = Str::kebab(class_basename($this->model)); // "CustomScript" -> "custom-script" 
        
        return match($action) {
            'view'   => "$prefix.view",
            'edit'   => "$prefix.edit",
            'delete' => "$prefix.delete",
            'pdf'    => "$prefix.export-pdf",
            'excel'  => "$prefix.export-excel",
            default  => "$prefix.$action",
        };
    }

    public function getRoute(string $action): string
    {
        if (isset($this->actions[$action])) {
            return $this->actions[$action];
        }

        $prefix = $this->model->route_prefix ?? Str::kebab(Str::plural(class_basename($this->model)));
        
        try {
            return match($action) {
                'view'   => route("$prefix.show", $this->model),
                'edit'   => route("$prefix.edit", $this->model),
                // 'delete' => '#',
                'delete' => $this->actions[$action] ?? route("$prefix.destroy", $this->model),
                'pdf'    => route("$prefix.export", [$this->model, 'pdf']),
                'excel'  => route("$prefix.export", [$this->model, 'excel']),
                default  => '#',
            };
        } catch (\Exception $e) {
            return '#';
        }
    }

    public function getLabel(string $action): string
    {
        return $this->actions[$action . '_label'] ?? __(Str::title($action));
    }

    public function getIcon(string $action): string
    {
        return match($action) {
            'view'   => 'bi bi-eye',
            'edit'   => 'bi bi-pencil',
            'delete' => 'bi bi-trash2',
            'pdf'    => 'bi bi-file-earmark-pdf',
            'excel'  => 'bi bi-file-earmark-excel',
            default  => 'bi bi-earmark-file',
        };
    }

    public function render()
    {
        return view('components.action-dropdown');
    }
}
