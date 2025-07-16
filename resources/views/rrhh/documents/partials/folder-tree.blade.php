@foreach($items as $name => $item)
    <div class="tree-item level-{{ $level }}">
        <div class="item-info">
            @if($item['type'] === 'folder')
                <span class="folder-toggle" data-target="#folder-{{ Str::slug($name) }}-{{ $level }}">
                    <i class="fas fa-folder folder-icon"></i>
                </span>
                <strong>{{ $item['name'] }}</strong>
                <small class="text-muted ml-2">(carpeta)</small>
            @else
                <i class="fas fa-file file-icon"></i>
                {{ $item['name'] }}
            @endif
        </div>
        
        @if($item['type'] === 'file' && isset($item['size']))
            <span class="file-size">
                {{ App\Utils\FileHelper::formatFileSize($item['size']) }}
            </span>
        @endif
    </div>
    
    @if($item['type'] === 'folder' && isset($item['children']))
        <div id="folder-{{ Str::slug($name) }}-{{ $level }}" style="display: none;">
            @include('rrhh.documents.partials.folder-tree', [
                'items' => $item['children'], 
                'level' => $level + 1
            ])
        </div>
    @endif
@endforeach
