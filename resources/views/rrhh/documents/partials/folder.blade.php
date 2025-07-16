{{-- Partial para mostrar la estructura de una carpeta de forma recursiva --}}
@php
    $isRoot = $level === 0;
    $treeClass = 'tree-level-' . $level;
    if ($isRoot) {
        $treeClass .= ' tree-level-0';
    }
@endphp

<div class="tree-item {{ $treeClass }}">
    @if(isset($folderData['files']) || isset($folderData['folders']))
        {{-- Es una carpeta --}}
        <div class="tree-folder">
            <span class="folder-toggle">
                <i class="fas fa-chevron-down text-muted mr-1"></i>
                <i class="fas fa-folder text-warning mr-2"></i>
                <strong>{{ $folderName }}</strong>
                @if(isset($folderData['total_files']))
                    <small class="text-muted ml-2">({{ $folderData['total_files'] }} archivos)</small>
                @endif
            </span>
            
            <div class="folder-contents ml-3">
                {{-- Mostrar subcarpetas --}}
                @if(isset($folderData['folders']))
                    @foreach($folderData['folders'] as $subFolderName => $subFolderData)
                        @include('rrhh.documents.partials.folder', [
                            'folderData' => $subFolderData, 
                            'folderName' => $subFolderName, 
                            'level' => $level + 1
                        ])
                    @endforeach
                @endif
                
                {{-- Mostrar archivos --}}
                @if(isset($folderData['files']))
                    @foreach($folderData['files'] as $file)
                        <div class="tree-file d-flex align-items-center">
                            @php
                                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                                $iconClass = 'fas fa-file text-secondary';
                                $iconColor = 'text-secondary';
                                
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
                                    case 'svg':
                                        $iconClass = 'fas fa-file-image text-info';
                                        break;
                                    case 'mp4':
                                    case 'avi':
                                    case 'mov':
                                    case 'wmv':
                                    case 'flv':
                                        $iconClass = 'fas fa-file-video text-purple';
                                        break;
                                    case 'mp3':
                                    case 'wav':
                                    case 'ogg':
                                    case 'flac':
                                        $iconClass = 'fas fa-file-audio text-success';
                                        break;
                                    case 'zip':
                                    case 'rar':
                                    case '7z':
                                    case 'tar':
                                    case 'gz':
                                        $iconClass = 'fas fa-file-archive text-dark';
                                        break;
                                    case 'txt':
                                        $iconClass = 'fas fa-file-alt text-muted';
                                        break;
                                    case 'css':
                                    case 'js':
                                    case 'html':
                                    case 'php':
                                    case 'py':
                                    case 'java':
                                    case 'cpp':
                                    case 'c':
                                        $iconClass = 'fas fa-file-code text-info';
                                        break;
                                }
                            @endphp
                            
                            <i class="{{ $iconClass }} mr-2"></i>
                            <span class="flex-grow-1">{{ $file['name'] }}</span>
                            @if(isset($file['size']))
                                <span class="file-size ml-2">
                                    {{ App\Utils\FileHelper::formatFileSize($file['size']) }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @else
        {{-- Es un archivo individual --}}
        <div class="tree-file d-flex align-items-center">
            @php
                $extension = strtolower(pathinfo($folderName, PATHINFO_EXTENSION));
                $iconClass = 'fas fa-file text-secondary';
                
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
                    case 'svg':
                        $iconClass = 'fas fa-file-image text-info';
                        break;
                    case 'mp4':
                    case 'avi':
                    case 'mov':
                    case 'wmv':
                    case 'flv':
                        $iconClass = 'fas fa-file-video text-purple';
                        break;
                    case 'mp3':
                    case 'wav':
                    case 'ogg':
                    case 'flac':
                        $iconClass = 'fas fa-file-audio text-success';
                        break;
                    case 'zip':
                    case 'rar':
                    case '7z':
                    case 'tar':
                    case 'gz':
                        $iconClass = 'fas fa-file-archive text-dark';
                        break;
                    case 'txt':
                        $iconClass = 'fas fa-file-alt text-muted';
                        break;
                    case 'css':
                    case 'js':
                    case 'html':
                    case 'php':
                    case 'py':
                    case 'java':
                    case 'cpp':
                    case 'c':
                        $iconClass = 'fas fa-file-code text-info';
                        break;
                }
            @endphp
            
            <i class="{{ $iconClass }} mr-2"></i>
            <span class="flex-grow-1">{{ $folderName }}</span>
            @if(isset($folderData['size']))
                <span class="file-size ml-2">
                    {{ App\Utils\FileHelper::formatFileSize($folderData['size']) }}
                </span>
            @endif
        </div>
    @endif
</div>
