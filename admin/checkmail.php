<?php

$root = $_SERVER['DOCUMENT_ROOT'] ; // donne le repertoire (à partir du lecteur) du root du seveur
$self = $_SERVER['PHP_SELF'] ; // donne l'adresse relative du fichier executé (sans l'url de base/port du site)
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); //suppression du nom du fichier
$self = mb_substr($self,0,-mb_strlen(strrchr($self,"/"))); // idem, auquel on a enlevé le dernier repertoire. Vu qu'on est dans admin, on trouve le reperoitre de base des galleries
$base = $root.$self; // collage du document root avec le/les repertoires pour atteindre le fichier executé

set_include_path(get_include_path().PATH_SEPARATOR.$base."/".'include');

include 'functions.php';
$galleries=getGalleries();

$account="beckfanelli.listen";
$password="beckfanelli0610";

//Tout d'abord, on ouvre une boite mail
$mail = imap_open("{pop.gmail.com:995/pop3/ssl/novalidate-cert}INBOX",$account,$password);

//Quitte à la faire, autant le faire pour chaque message !
$nbmess = imap_num_msg($mail);
echo "nombre :".$nbmess;

if ($nbmess == 0)
	{
	print "
	<div align='center'>
	  <b>Aucun message présent sur le serveur</b>
          <br /><br />
	</div>
      ";
	} 
else
	{  
	for($j=1;$j<=$nbmess;$j++)
		{
		//Extraction du sujet du message, pour ceux qui voudrait faire un test sur un titre au préalable
		$header = imap_headerinfo($mail,$j);
		$sujet = strtolower($header->subject);
		$erreur = 0; // par defaut, pas d'erreur
		$raison="";
		
		$erreur = 1; // on predit une erreur, si on trouve qu'une gallerie possède le nom dans le sujet, on remet la variable à "pas d'erreur"
		foreach($galleries as $gallerie)  {
		if ($gallerie == $sujet )
			$erreur=0;
		}
		if ($erreur==1)
			$raison.="Le nom la gallerie doit être le sujet de votre mail";
		
		if ($erreur==0)
			{
			//Extraction de la structure du message	
			$struct = imap_fetchstructure($mail,$j);

			// On compte le nombre de partie dans la structure du message
			if ($struct->type == 1)
			{
			$nbrparts = !$struct->parts ? "1" : count($struct->parts);
			}

			//On place le code binaire de la pièce dans un tableau
			$piece = array();
//			echo "nombre de partie :".$nbrparts."<br/>";
			for($h=2;$h<=$nbrparts;$h++)
				{
//				echo "partie =>".$h."<br/>";
				
				$part = $struct->parts[1] ; 

				//Extraction du code binaire de la pièce jointe
				$piece = imap_fetchbody($mail,$j,$h);
				/*print_r($struct->parts[0]);
				echo "<br/>";
				print_r($struct->parts[1]);
				echo "<br/>";
				print_r($struct->parts[2]);*/
//				echo "<br/>";
//				echo "valeur : ";
				//print_r($struct->parts[1]); 
				//Le 3 est spécifique à l'encodage en base64 (le plus répandu) pour les pièces jointes.
				if (isset($part))
					{
					echo "set";
					if (property_exists($part, "encoding"))
						{
//						echo " propertyexists";
						if ($part->encoding == "3")
							{
							//echo "partie 3<br/>";
							//Comptage du nombre de parametres
							$nbparam =count($part->parameters);
							
							$i=0;
							while ($i < $nbparam) 
								{
								$param = $part->parameters[$i];
								//$nom_fichier = $struct->parts[$h]->dparameters[0]->value; 
								$nom_fichier = $struct->parts[$h-1]->dparameters[0]->value; 
								if($nom_fichier!=null)
									{
									echo '&nbsp;&nbsp;&nbsp;&nbsp;/'.$nom_fichier.'<br>';
									}
								$i++;
								} 
							$piece = imap_base64($piece); 
							}
						}
					}
				if(isset($nom_fichier)) {if ($nom_fichier!=null)
					{
					$nom_fichier = str_replace(".doc","",$nom_fichier) ;
					echo $base."/".$sujet."/easyupload/"."pj".$h."_".$nom_fichier."<br/>";
					//Ouverture du fichier et création s'il n'existe pas
					$newfichier = fopen($base."/".$sujet."/easyupload/"."pj".$h."_".$nom_fichier,"w+");
					//Ecriture dans le fichier
					fwrite($newfichier,$piece);
					//Fermeture du fichier
					fclose($newfichier);
				//	imap_delete($mail, "$j:$j");
					}
				else
					{
					$erreur=1;
					$raison="Il n'y a aucune piece jointe dans votre e-mail";
					}}
				}
			}
		if ($erreur==1)
			{
			echo "erreur : envoyé à ".$header->from[0]->mailbox."@".$header->from[0]->host;
			
			$headers  = "MIME-Version: 1.0 \n";
			$headers .='Content-Transfer-Encoding: 8bit';
			$headers .= "Content-type: text/html; charset=utf-8 \n";
			$headers .="From: beckfanelli.listen@gmail.com  \n";
			mail ($header->from[0]->mailbox."@".$header->from[0]->host, "RE: ".$header->subject, "Votre e-mail n'a pas pu être traité pour la raison suivante :".$raison, $headers);
			}
		imap_delete($mail, "$j:$j");
		}
	imap_expunge($mail);
	}
imap_close($mail);
?>