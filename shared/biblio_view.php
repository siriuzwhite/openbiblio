<?php
/* This file is part of a copyrighted work; it is distributed with NO WARRANTY.
 * See the file COPYRIGHT.html for more details.
 */
 
  require_once("../shared/common.php");
  
  #****************************************************************************
  #*  Checking for get vars.  Go back to form if none found.
  #****************************************************************************
  if (count($_GET) == 0) {
    header("Location: ../catalog/index.php");
    exit();
  }

  #****************************************************************************
  #*  Checking for tab name to show OPAC look and feel if searching from OPAC
  #****************************************************************************
  if (isset($_GET["tab"])) {
    $tab = $_GET["tab"];
  } else {
    $tab = "cataloging";
  }

  $nav = "view";
  if ($tab != "opac") {
    require_once("../shared/logincheck.php");
  }
  require_once("../classes/Biblio.php");
  require_once("../classes/BiblioQuery.php");
  require_once("../classes/BiblioCopy.php");
  require_once("../classes/BiblioCopyQuery.php");
  require_once("../classes/DmQuery.php");
  require_once("../classes/UsmarcTagDm.php");
  require_once("../classes/UsmarcTagDmQuery.php");
  require_once("../classes/UsmarcSubfieldDm.php");
  require_once("../classes/UsmarcSubfieldDmQuery.php");
  require_once("../functions/marcFuncs.php");
  require_once("../classes/Localize.php");
  $loc = new Localize(OBIB_LOCALE,"shared");


  #****************************************************************************
  #*  Retrieving get var
  #****************************************************************************
  $bibid = $_GET["bibid"];
  if (isset($_GET["msg"])) {
    $msg = "<font class=\"error\">".H($_GET["msg"])."</font><br><br>";
  } else {
    $msg = "";
  }

  #****************************************************************************
  #*  Loading a few domain tables into associative arrays
  #****************************************************************************
  $dmQ = new DmQuery();
  $dmQ->connect();
  $collectionDm = $dmQ->getAssoc("collection_dm");
  $materialTypeDm = $dmQ->getAssoc("material_type_dm");
  $biblioStatusDm = $dmQ->getAssoc("biblio_status_dm");
  $dmQ->close();

  $marcTagDmQ = new UsmarcTagDmQuery();
  $marcTagDmQ->connect();
  if ($marcTagDmQ->errorOccurred()) {
    $marcTagDmQ->close();
    displayErrorPage($marcTagDmQ);
  }
  $marcTagDmQ->execSelect();
  if ($marcTagDmQ->errorOccurred()) {
    $marcTagDmQ->close();
    displayErrorPage($marcTagDmQ);
  }
  $marcTags = $marcTagDmQ->fetchRows();
  $marcTagDmQ->close();

  $marcSubfldDmQ = new UsmarcSubfieldDmQuery();
  $marcSubfldDmQ->connect();
  if ($marcSubfldDmQ->errorOccurred()) {
    $marcSubfldDmQ->close();
    displayErrorPage($marcSubfldDmQ);
  }
  $marcSubfldDmQ->execSelect();
  if ($marcSubfldDmQ->errorOccurred()) {
    $marcSubfldDmQ->close();
    displayErrorPage($marcSubfldDmQ);
  }
  $marcSubflds = $marcSubfldDmQ->fetchRows();
  $marcSubfldDmQ->close();


  #****************************************************************************
  #*  Search database
  #****************************************************************************
  $biblioQ = new BiblioQuery();
  $biblioQ->connect();
  if ($biblioQ->errorOccurred()) {
    $biblioQ->close();
    displayErrorPage($biblioQ);
  }
  if (!$biblio = $biblioQ->doQuery($bibid, $tab)) {
    $biblioQ->close();
    displayErrorPage($biblioQ);
  }
  $biblioFlds = $biblio->getBiblioFields();

  #**************************************************************************
  #*  Show bibliography info.
  #**************************************************************************
  if ($tab == "opac") {
    require_once("../shared/header_opac.php");
    if (!$biblio->showInOpac()) {
      $biblio = $biblioQ->doQuery($bibid=0);
      $biblioFlds = $biblio->getBiblioFields();
    }
  } else {
    require_once("../shared/header.php");
  }

?>

<?php echo $msg ?>
<div style="float:left;margin-right:10px;">
<table class="primary">
  <tr>
    <th align="left" colspan="2" nowrap="yes">
      <?php echo $loc->getText("biblioViewTble1Hdr"); ?>:
    </th>
  </tr>
  <tr>	
    <td nowrap="true" class="primary" valign="top">
      <?php echo $loc->getText("biblioViewMaterialType"); ?>:
    </td>
    <td valign="top" class="primary">
      <?php echo H($materialTypeDm[$biblio->getMaterialCd()]);?>
    </td>
  </tr>
  <tr>
    <td nowrap="true" class="primary" valign="top">
      <?php echo $loc->getText("biblioViewCollection"); ?>:
    </td>
    <td valign="top" class="primary">
      <?php echo H($collectionDm[$biblio->getCollectionCd()]);?>
    </td>
  </tr>
  <tr>
    <td class="primary" valign="top">
      <?php echo "ID"; ?>:
    </td>
    <td valign="top" class="primary">
      <?php echo $biblio->getBibid(); ?>
    </td>
  </tr>
  <tr>
    <td class="primary" valign="top">
      <?php echo $loc->getText("biblioViewCallNmbr"); ?>:
    </td>
    <td valign="top" class="primary">
      <?php echo H($biblio->getCallNmbr1()); ?>
      <?php echo H($biblio->getCallNmbr2()); ?>
      <?php echo H($biblio->getCallNmbr3()); ?>
    </td>
  </tr>
  <tr>
    <td class="primary" valign="top">
      <?php printUsmarcText(245,"a",$marcTags, $marcSubflds, FALSE);?>:
    </td>
    <td valign="top" class="primary">
      <?php if (isset($biblioFlds["245a"])) echo H($biblioFlds["245a"]->getFieldData());?>
    </td>
  </tr>
  <tr>
    <td class="primary" valign="top">
      <?php printUsmarcText(245,"b",$marcTags, $marcSubflds, FALSE);?>:
    </td>
    <td valign="top" class="primary">
      <?php if (isset($biblioFlds["245b"])) echo H($biblioFlds["245b"]->getFieldData());?>
    </td>
  </tr>
  <tr>
    <td class="primary" valign="top">
      <?php printUsmarcText(100,"a",$marcTags, $marcSubflds, FALSE);?>:
    </td>
    <td valign="top" class="primary">
      <?php if (isset($biblioFlds["100a"])) echo H($biblioFlds["100a"]->getFieldData());?>
    </td>
  </tr>
  <tr>
    <td nowrap="true" class="primary" valign="top">
      <?php printUsmarcText(245,"c",$marcTags, $marcSubflds, FALSE);?>:
    </td>
    <td valign="top" class="primary">
      <?php if (isset($biblioFlds["245c"])) echo H($biblioFlds["245c"]->getFieldData());?>
    </td>
  </tr>
  <tr>
    <td nowrap="true" class="primary" valign="top">
      <?php echo $loc->getText("biblioViewOpacFlg"); ?>:
    </td>
    <td valign="top" class="primary">
      <?php if ($biblio->showInOpac()) {
        echo $loc->getText("biblioViewYes");
      } else {
        echo $loc->getText("biblioViewNo");
      }?>
    </td>
  </tr>
</table>
<br />
</div>

<?php
  #****************************************************************************
  #*  Show picture of the Bibliography if defined
  #****************************************************************************
if (isset($biblioFlds["902a"]))
{
?>
<div>
<table class="primary">
  <tr>
    <th align="left" colspan="2" nowrap="yes">
      <?php echo $loc->getText("biblioViewPictureHeader"); ?>:
    </th>
  </tr>
  <tr>	
    <td valign="top" class="primary">
      <a href="../pictures/<?php echo $biblioFlds["902a"]->getFieldData();?>"><img src="../pictures/<?php echo $biblioFlds["902a"]->getFieldData();?>" width="250"></a>
    </td>
  </tr>
</table>
</div>
<br />
<?
}
?>
<br style="clear:both" />
<?

  #****************************************************************************
  #*  Show copy information
  #****************************************************************************
  if ($tab == "cataloging") { ?>
    <a href="../catalog/biblio_copy_new_form.php?bibid=<?php echo HURL($bibid);?>&reset=Y">
      <?php echo $loc->getText("biblioViewNewCopy"); ?></a><br/>
    <?php
    $copyCols=7;
  } else {
    $copyCols=5;
  }

  $copyQ = new BiblioCopyQuery();
  $copyQ->connect();
  if ($copyQ->errorOccurred()) {
    $copyQ->close();
    displayErrorPage($copyQ);
  }
  if (!$copy = $copyQ->execSelect($bibid)) {
    $copyQ->close();
    displayErrorPage($copyQ);
  }
?>

<h1><?php echo $loc->getText("biblioViewTble2Hdr"); ?>:</h1>
<table class="primary">
  <tr>
    <?php if ($tab == "cataloging") { ?>
      <th colspan="2" nowrap="yes">
        <?php echo $loc->getText("biblioViewTble2ColFunc"); ?>
      </th>
    <?php } ?>
    <th align="left" nowrap="yes">
      <?php echo $loc->getText("biblioViewTble2Col1"); ?>
    </th>
    <th align="left" nowrap="yes">
      <?php echo $loc->getText("biblioViewTble2Col2"); ?>
    </th>
    <th align="left" nowrap="yes">
      <?php echo $loc->getText("biblioViewTble2Col3"); ?>
    </th>
    <th align="left" nowrap="yes">
      <?php echo $loc->getText("biblioViewTble2Col4"); ?>
    </th>
    <th align="left" nowrap="yes">
      <?php echo $loc->getText("biblioViewTble2Col5"); ?>
    </th>
  </tr>
  <?php
    if ($copyQ->getRowCount() == 0) { ?>
      <tr>
        <td valign="top" colspan="<?php echo H($copyCols); ?>" class="primary" colspan="2">
          <?php echo $loc->getText("biblioViewNoCopies"); ?>
        </td>
      </tr>      
    <?php } else {
      $row_class = "primary";
      while ($copy = $copyQ->fetchCopy()) {
  ?>
    <tr>
      <?php if ($tab == "cataloging") { ?>
        <td valign="top" class="<?php echo H($row_class);?>">
          <a href="../catalog/biblio_copy_edit_form.php?bibid=<?php echo HURL($copy->getBibid());?>&amp;copyid=<?php echo H($copy->getCopyid());?>" class="<?php echo H($row_class);?>"><?php echo $loc->getText("biblioViewTble2Coledit"); ?></a>
        </td>
        <td valign="top" class="<?php echo H($row_class);?>">
          <a href="../catalog/biblio_copy_del_confirm.php?bibid=<?php echo HURL($copy->getBibid());?>&amp;copyid=<?php echo HURL($copy->getCopyid());?>" class="<?php echo H($row_class);?>"><?php echo $loc->getText("biblioViewTble2Coldel"); ?></a>
        </td>
      <?php } ?>
      <td valign="top" class="<?php echo H($row_class);?>">
        <?php echo H($copy->getBarcodeNmbr()); ?>
      </td>
      <td valign="top" class="<?php echo H($row_class);?>">
        <?php echo H($copy->getCopyDesc()); ?>
      </td>
      <td valign="top" class="<?php echo H($row_class);?>">
        <?php echo H($biblioStatusDm[$copy->getStatusCd()]); ?>
      </td>
      <td valign="top" class="<?php echo H($row_class);?>">
        <?php echo H($copy->getStatusBeginDt()); ?>
      </td>
      <td valign="top" class="<?php echo H($row_class);?>">
        <?php echo H($copy->getDueBackDt()); ?>
      </td>
    </tr>      
  <?php
        # swap row color
        if ($row_class == "primary") {
          $row_class = "alt1";
        } else {
          $row_class = "primary";
        }
      }
      $copyQ->close();
    } ?>
</table>





<br />
<table class="primary">
  <tr>
    <th align="left" colspan="2" nowrap="yes">
      <?php echo $loc->getText("biblioViewTble3Hdr"); ?>:
    </th>
  </tr>
  <?php
    $displayCount = 0;
    foreach ($biblioFlds as $key => $field) {
      if (($field->getFieldData() != "") 
        && ($key != "245a")
        && ($key != "245b")
        && ($key != "245c")
        && ($key != "902a")
        && ($key != "100a")) {
        $displayCount = $displayCount + 1;
  ?>
        <tr>
          <td valign="top" class="primary">
            <?php printUsmarcText($field->getTag(),$field->getSubfieldCd(),$marcTags, $marcSubflds, FALSE);?>:
          </td>
          <td valign="top" class="primary"><?php echo H($field->getFieldData()); ?></td>
        </tr>      
  <?php
      }
    }
    if ($displayCount == 0) {
  ?>
        <tr>
          <td valign="top" class="primary" colspan="2">
            <?php echo $loc->getText("biblioViewNoAddInfo"); ?>
          </td>
        </tr>      
  <?php
    }
  ?>
</table>

<br />
<a name="form"></a>
<h1>Interesse bekunden:</h1>
<?php 
	
	//mail stuff
	
	$to_mail = "m.rochow@rpk-leipzig.de";
	
	if ($_GET['action'] == 'sendmail') {
		if (empty($_POST['name'])) {
			echo "<font color=\"red\">Bitte Namen angeben!</font><br />";
		} else {
			
			include_once("../classes/PHPMailerAutoload.php");
			
			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = 'smtp.gmail.com';
			$mail->Port = '587';
			$mail->SMTPAuth = true;
			$mail->Username = 'rpkmailer@gmail.com';
			$mail->Password = 'rpkmailer01';
			$mail->SMTPSecure = 'tls'; 
			
			
			$mail->From = 'rpkmailer@gmail.com';
			$mail->FromName = 'RPK Mailer';
			$mail->addAddress($to_mail );

			$mail->isHTML(true);

			$mail->Subject = "[RPK Bibliothek] Interesse an " . $biblioFlds["245a"]->getFieldData();
			
			$content .= "Der Benutzer <b>\"" . $_POST['name'] . "\"</b> hat Interesse an:\n";
			$content .= "<a href=\"".$_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "?bibid=".$bibid."&tab=cataloging\">" . $biblioFlds["245a"]->getFieldData() ."</a>";
			
			$content .= "<br><br><u>Nachricht des Benutzers:</u><br>";
			$content .= $_POST['message'];
			
			$mail->Body = $content;

			if(!$mail->send()) {
				 echo "<font color=\"red\">Fehleraufgetreten!</font><br />";
				 echo 'Mailer Error: ' . $mail->ErrorInfo . "<br />";
			} else {
				echo "<font color=\"green\">Nachricht wurde versand!</font><br />";
			}
		}
	}

?>
<form action="<?=$_SERVER['PHP_SELF']."?bibid=".$bibid."&tab=opac&action=sendmail#form";?>" method="POST">
	<input type="hidden" name="bibid" value="<?=$bibid;?>" />
	<table class="primary">
		<tbody>
			<tr>
				<th align="left" colspan="2" nowrap="yes">
					Kontaktformular
				</th>
			</tr>
			<tr>
				<td class="primary">Name:</td>
				<td class="primary"><input name="name" style="width: 200px;" type="text" /></td>
			</tr>
			<tr>
				<td class="primary">Nachricht<br>(optional):</td>
				<td class="primary">
					<textarea type="text" style="border-style: inset; border-width: 2px; width: 200px;" name="message" rows="5" cols></textarea>
			</tr>
			<tr>
				<td class="primary"></td>
				<td class="primary"><input style="vertical-align: right;" type="submit" /></td>
			</tr>
		</tbody>
	</table>
</form>


<?php require_once("../shared/footer.php"); ?>
