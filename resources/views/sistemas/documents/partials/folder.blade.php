@php
    $hasFiles = isset($folderData['files']) && is_array($folderData['files']) && count($folderData['files']) > 0;
    $hasFolders = isset($folderData['folders']) && is_array($folderData['folders']) && count($folderData['folders']) > 0;
@endphp

<div class="tree-item tree-level-{{ $level }}">
    @if($hasFolders || $hasFiles)
        <div class="folder-toggle d-flex align-items-center">
            <i class="fas fa-chevron-down mr-2"></i>
            <i class="fas fa-folder text-warning mr-2"></i>
            <span class="tree-folder">{{ $folderName }}</span>
        </div>
        
        <div class="folder-contents">
            @if($hasFolders)
                @foreach($folderData['folders'] as $subFolderName => $subFolderData)
                    @include('sistemas.documents.partials.folder', [
                        'folderData' => $subFolderData, 
                        'folderName' => $subFolderName, 
                        'level' => $level + 1
                    ])
                @endforeach
            @endif
            
            @if($hasFiles)
                @foreach($folderData['files'] as $file)
                    <div class="tree-file d-flex align-items-center">
                        @php
                            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                            $iconClass = 'fas fa-file text-muted';
                            
                            switch($extension) {
                                case 'pdf':
                                    $iconClass = 'fas fa-file-pdf text-danger';
                                    break;
                                case 'doc':
                                case 'docx':
                                    $iconClass = 'fas fa-file-word text-primary';
                                    break;
                                case 'xls':
                                case 'xlsx':
                                    $iconClass = 'fas fa-file-excel text-success';
                                    break;
                                case 'ppt':
                                case 'pptx':
                                    $iconClass = 'fas fa-file-powerpoint text-warning';
                                    break;
                                case 'jpg':
                                case 'jpeg':
                                case 'png':
                                case 'gif':
                                case 'bmp':
                                    $iconClass = 'fas fa-file-image text-info';
                                    break;
                                case 'txt':
                                    $iconClass = 'fas fa-file-alt text-secondary';
                                    break;
                                case 'zip':
                                case 'rar':
                                case '7z':
                                    $iconClass = 'fas fa-file-archive text-dark';
                                    break;
                                default:
                                    $iconClass = 'fas fa-file text-muted';
                            }
                        @endphp
                        
                        <i class="{{ $iconClass }}"></i>
                        <span class="flex-grow-1">{{ $file['name'] }}</span>
                        <span class="file-size">
                            @if(isset($file['size']))
                                @php
                                    $size = $file['size'];
                                    if ($size >= 1073741824) {
                                        $formatted = number_format($size / 1073741824, 2) . ' GB';
                                    } elseif ($size >= 1048576) {
                                        $formatted = number_format($size / 1048576, 2) . ' MB';
                                    } elseif ($size >= 1024) {
                                        $formatted = number_format($size / 1024, 2) . ' KB';
                                    } else {
                                        $formatted = $size . ' bytes';
                                    }
                                @endphp
                                {{ $formatted }}
                            @endif
                        </span>
                    </div>
                @endforeach
            @endif
        </div>
    @else
        <div class="d-flex align-items-center">
            <i class="fas fa-folder text-warning mr-2"></i>
            <span class="tree-folder">{{ $folderName }} (vacía)</span>
        </div>
    @endif
</div>
