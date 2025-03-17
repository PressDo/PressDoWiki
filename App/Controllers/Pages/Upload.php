<?php
namespace PressDo\App\Controllers\Pages;

use PressDo\App\Models\{Document,Member,Files};
use PressDo\App\Core\Controller;
use PressDo\App\Controllers\ACL as WikiACL;
use PressDo\App\Helpers\{Languages,Config,Namespaces,DefaultConfig};

class Upload extends Controller
{
    public function makeData(): array
    {
        if (!DefaultConfig::get('wiki.file_upload')) {
            $error = 'err_file_upload_disabled';
        }

        if (!empty($_POST['document']) && count($_FILES)) {
            $fileExt = str_replace(['JPEG', 'jpeg'], 'jpg', implode('', array_slice(explode('.', $_FILES['file']['name']), -1, 1)));
            $docExt = implode('', array_slice(explode('.', $_POST['document']), -1, 1));
            $allowedExts = ['jpg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico'];
            [$namespace, $title] = self::parseTitle($_POST['document']);
            
            // 확장자 오류
            if (!in_array($docExt, $allowedExts) || $docExt !== strtolower($fileExt)) {
                $error = 'err_invalid_fileext';
            }

            // 사진 아님
            if (!str_contains($_FILES['file']['type'], 'image')) {
                $error = 'err_invalid_file';
            }

            // 용량 제한
            if ($_FILES['file']['error'] === 1) {
                $error = 'err_file_toobig';
            }

            // 이름공간 오류
            if ($namespace !== Namespaces::file()) {
                $error = 'err_invalid_file_namespace';
            }

            // 문서 중복
            if (Document::getUuid($namespace, $title)) {
                $error = 'err_document_exists';
            }

            // 정상 처리
            if (empty($error)) {
                // change some to webp
                if (in_array($fileExt, ['jpg', 'png', 'gif', 'webp', 'bmp'])) {
                    $size = getimagesize($_FILES['file']['tmp_name']);

                    switch ($fileExt) {
                        case 'jpg':
                            $img = imagecreatefromjpeg($_FILES['file']['tmp_name']);
                            if ($size[0] > 1000)
                                $img = imagescale($img, 1000);
                            imagewebp($img, $_FILES['file']['tmp_name']);
                            break;
                        case 'png':
                            $img = imagecreatefrompng($_FILES['file']['tmp_name']);
                            if ($size[0] > 1000)
                                $img = imagescale($img, 1000);
                            imagewebp($img, $_FILES['file']['tmp_name']);
                            break;
                        case 'gif':
                            $img = imagecreatefromgif($_FILES['file']['tmp_name']);
                            if ($size[0] > 1000)
                                $img = imagescale($img, 1000);
                            imagegif($img, $_FILES['file']['tmp_name']);
                            break;
                        case 'webp':
                            $img = imagecreatefromwebp($_FILES['file']['tmp_name']);
                            if ($size[0] > 1000)
                                $img = imagescale($img, 1000);
                            imagewebp($img, $_FILES['file']['tmp_name']);
                            break;
                        case 'bmp':
                            $img = imagecreatefrombmp($_FILES['file']['tmp_name']);
                            if ($size[0] > 1000)
                                $img = imagescale($img, 1000);
                            imagebmp($img, $_FILES['file']['tmp_name'], true);
                            break;
                    }
                }
                $size = getimagesize($_FILES['file']['tmp_name']);
                $hash = hash_file('sha256', $_FILES['file']['tmp_name']);

                $dup = Files::findHash($hash);

                if (!$dup) {
                    // hash 중복 아닌 경우에만 업로드
                    $className = 'PressDo\App\Services\Uploaders\\'.Config::get('storage.type');
                    $uploader = new $className();
                    $uploader->execute([
                        'path' => substr($hash, 0, 2).'/'.$hash.'.'.str_replace(['jpg', 'png'], 'webp', $fileExt),
                        'file' => $_FILES['file']['tmp_name']
                    ]);
                }

                $content = '[include('.Namespaces::template().':'.Languages::get('image_license').'/'.$_POST['license'].")]\n"
                    .'[['.Namespaces::category().':'.Namespaces::file().'/'.$_POST['category']."]]\n".$_POST['text'];
                
                $member = $this->session['member'] ? $this->session['uuid'] : null;
                $ip = !$member ? ($this->session['uuid'] ?? Member::getIpUuid($this->session['ip'])) : null;
                if (!$member && !$this->session['uuid']) {
                    $this->session['uuid'] = $ip;
                }

                $fileuuid = Document::create($namespace, $title);
                $comment = empty($_POST['summary']) ? sprintf(Languages::get('history', 'uploaded_file'), $_FILES['file']['name']) : $_POST['summary'];
                Document::save($fileuuid, $content, $comment, $member, $ip, 0, 0, 'create');
                Files::save($fileuuid, $hash, $size[0], $size[1]);

                Header('Location: /w/'.$_POST['document']);
                exit;
            } else {
                $this->error = [
                    'code' => $error,
                    'message' => sprintf(Languages::get('msg', $error), strtolower($fileExt)) ?? '',
                    'errbox' => true
                ];
            }
        }

        $dataset = Document::getLicensesAndCategories();
        foreach ($dataset['License'] as $k => $l) {
            $dataset['License'][$k] = substr($l['title'], strlen(Languages::get('image_license').'/'));
        }
        foreach ($dataset['Category'] as $k => $l) {
            $dataset['Category'][$k] = substr($l['title'], strlen(Namespaces::file().'/'));
        }
        $page = [
            'view_name' => 'Upload',
            'title' => Languages::get('page')['Upload'],
            'data' => [
                'Licenses' => $dataset['License'],
                'Categories' => $dataset['Category']
            ],
            'menus' => [],
            'customData' => []
        ];

        return $page;
    }
}