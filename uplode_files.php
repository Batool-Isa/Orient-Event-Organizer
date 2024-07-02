<?php
include 'header.php';
include 'Upload.php';

session_start();

if (isset($_POST['submitted'])) {

    if (!empty($_FILES)) {
        $upload = new Upload();
        $upload->setUploadDir('uploads/');

        // Ensure the upload directory exists and has the correct permissions
        if (!$upload->check_dir($upload->getUploadDir())) {
            if (!mkdir($upload->getUploadDir(), 0777, true)) {
                echo '<p class="error">Failed to create upload directory. Please check the parent directory permissions.</p>';
                exit;
            } else {
                chmod($upload->getUploadDir(), 0777);
            }
        }

        $msg = $upload->upload('name');

        if (empty($msg)) {
            echo '<p>File uploaded successfully: ' . $upload->getFilepath() . '</p>';
            // You can add further processing here, like saving file info to the database
        } else {
            foreach ($msg as $error) {
                echo '<p class="error">' . $error . '</p>';
            }
        }
    } else {
        echo '<p class="error">No file uploaded. Please try again.</p>';
    }
}

include 'header.html';

echo '<h1>Upload Files</h1>';

echo '<div><form action="upload_files.php" method="post" enctype="multipart/form-data">
        <fieldset>
            <label>File</label>
            <input type="file" name="name" />
            <div align="center">
                <input type="submit" name="submit" value="Upload" />
            </div>  
            <input type="hidden" name="submitted" value="TRUE" />
        </fieldset>
    </form></div>';

// list files here
// Assuming you have a class to handle files
$files = new Files();
$row = $files->getAllFiles();

if (!empty($row)) {
    echo '<br />';
    echo '<table align="center" cellspacing="2" cellpadding="4" width="75%">';
    echo '<tr bgcolor="#87CEEB">
          <td><b>Edit</b></td>
          <td><b>Delete</b></td>
          <td><b>File Name</b></td>
          <td><b>File Type</b></td>
          <td><b>File Location</b></td>
          </tr>';

    $bg = '#eeeeee';

    for ($i = 0; $i < count($row); $i++) {
        $bg = ($bg == '#eeeeee' ? '#ffffff' : '#eeeeee');

        echo '<tr bgcolor="' . $bg . '">
             <td><a href="edit_file.php?fid=' . $row[$i]->fid . '">Edit</a></td>
             <td><a href="delete_file.php?fid=' . $row[$i]->fid . '">Delete</a></td>
             <td><a target="_blank" href="view_file.php?fid=' . $row[$i]->fid . '">' . $row[$i]->fname . '</a></td>
             <td>' . $row[$i]->ftype . '</td>
             <td><img src="' . $row[$i]->flocation . '" width="20%" height="20%"></td>
             </tr>';
    }
    echo '</table>';
} else {
    echo '<p class="error">No files are uploaded yet</p>';
}

include 'footer.html';
?>
