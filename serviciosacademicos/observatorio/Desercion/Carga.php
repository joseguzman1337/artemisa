<?PHP 
session_start();
/*if(!isset ($_SESSION['MM_Username'])){
	echo '<blink><strong style="color:#F00; font-size:18px">No ha iniciado sesi�n en el sistema</strong></blink>';
	exit();
} */

#phpinfo();

$TypeFormulario          = $_REQUEST['tipo'];
$Periodo			     = $_REQUEST['periodo'];

if($TypeFormulario==0 || $TypeFormulario=='0'){
    
    $Typo_text      = 'Desercion';
    $NombreCarrera  = 'Desercion Nacional';
}else{
    $NombreCarrera  = 'Retencion Nacional';
    $Typo_text      = 'Retencion'; 
}

##########################################################
            
    include ('Desercion_class.php');  $C_Desercion = new Desercion();
   
    include("../../mgi/pChart/class/pData.class.php");
   
    include("../../mgi/pChart/class/pDraw.class.php");
    
    include("../../mgi/pChart/class/pImage.class.php");
   
    $fontPath = "../../mgi/pChart/fonts/";
    global $db;
    MainJson();
    
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>Grafica Desercion</title>
            <link rel="stylesheet" href="../../mgi/css/cssreset-min.css" type="text/css" /> 
            <link rel="stylesheet" href="../../css/demo_page.css" type="text/css" /> 
            <link rel="stylesheet" href="../../css/demo_table_jui.css" type="text/css" /> 
            <link rel="stylesheet" href="../../css/themes/smoothness/jquery-ui-1.8.4.custom.css" type="text/css" /> 
            <link rel="stylesheet" href="../../mgi/css/styleMonitoreo.css" type="text/css" /> 
            <link rel="stylesheet" href="../../mgi/css/styleDatos.css" type="text/css" /> 
            
            <script type="text/javascript" language="javascript" src="../../mgi/js/jquery.js"></script>
            <script type="text/javascript" language="javascript" src="../../mgi/js/jquery.dataTables.js"></script>
            <script type="text/javascript" language="javascript" src="../../mgi/js/jquery-ui-1.8.21.custom.min.js"></script>   
            <script type="text/javascript" language="javascript" src="../../mgi/js/jquery.fastLiveFilter.js"></script>     
            <script type="text/javascript" language="javascript" src="../../mgi/js/nicEdit.js"></script>
            <script type="text/javascript" language="javascript" src="../../mgi/js/functions.js"></script>  
            <script type="text/javascript" language="javascript" src="../../mgi/js/functionsMonitoreo.js"></script> 
            <script type="text/javascript" language="javascript" src="../../mgi/js/plusTabs.js"></script>
       </head>
       <body class="body">
       <div class="grafica" align="center">
       <?PHP 
        $CodigoPeriodo	= $Periodo;
        //$CodigoCarrera  = $_REQUEST['CodigoCarrera'];
        
        $Periodo_Actual = $C_Desercion->Periodo('Actual','','');
        //$C_Datos        = $C_Desercion->CadenaPrograma($CodigoPeriodo,$CodigoCarrera);
        $C_Total        = $C_Desercion->CadenaInstitucional($CodigoPeriodo);
        
        //echo '<pre>';print_r($C_Total);die;   /
        /*
       
        
        /*
        Si nO genera la Grafica Antes de Modificar el Codigo mirar los permisos de las carpetas que contiene las graficas ddeben ser 777
        */ 
        /*echo '<pre>';print_r($C_Datos);
        echo '<pre>';print_r($D_Total);*/
        //var_dump($C_Datos);
        /***************************************/
        $arrayP = str_split($CodigoPeriodo, strlen($CodigoPeriodo)-1);
        
        $P_Periodo=$arrayP[0]."-".$arrayP[1];
        
        $arrayA = str_split($Periodo_Actual, strlen($Periodo_Actual)-1);
        
        $Ac_Periodo=$arrayA[0]."-".$arrayA[1];
        /***************************************/
        
        $SQL_N='SELECT 
                id_datosNacionales,
                valor,
                periodo
                
                FROM 
                
                DatosNacionales
                
                WHERE
                
                tipo="'.$TypeFormulario.'"
                AND
                periodo>="'.$Periodo.'"
                AND
                codigoestado=100';
                
            if($DataNacional=&$db->Execute($SQL_N)===false){
                echo 'Error en el SQL Naacional...<br><br>'.$SQl_N;
                die;
            }    
        
        $C_Nacional = array();
        
        while(!$DataNacional->EOF){
            
            $C_Nacional['Periodo'][] = $DataNacional->fields['periodo'];
            $C_Nacional['Porcentaje'][] = $DataNacional->fields['valor'];
            
            $DataNacional->MoveNext();
        }//while
        
        $D_Total = array();
        
        for($i=0;$i<count($C_Nacional['Periodo']);$i++){
            
            if($TypeFormulario==0 || $TypeFormulario=='0'){
                $D_Total['Total_D'][] = $C_Total['Total_D'][$i];
            }else{
                $D_Total['Total_D'][] = 100-$C_Total['Total_D'][$i];                
            }
            
            
            $D_Total['Periodo'][] = $C_Total['Periodo'][$i];
            
        }
        
       //echo '<pre>';print_r($D_Total);die;
        
        
        
        $N=max($C_Nacional["Porcentaje"]);
        $K=max($D_Total['Total_D']);
        
        //echo '<br>N->'.$N.'<br>$k->'.$K;
        if($N>$K){
            //echo 'N es mayor que K';
            $Max    = $N;
        }else{
            //echo 'N es Menor que K';
            $Max    = $K;
        }
        /*******************************************************************************************************/
        $MyData = new pData();
        
       
        
        $MyData->addPoints($C_Nacional["Porcentaje"],$NombreCarrera." ".$P_Periodo."-".$Ac_Periodo);
        $MyData->addPoints($D_Total['Total_D'],"Desercion Pregrado".$P_Periodo."-".$Ac_Periodo);
        $MyData->setSerieTicks($Typo_text." Pregrado".$P_Periodo."-".$Ac_Periodo,4);   
        
        $MyData->setAxisName(0,$Typo_text." %");
        $MyData->addPoints($C_Nacional["Periodo"],"Labels");
        $MyData->setSerieDescription("Labels","Periodo");
        $MyData->setAbscissa("Labels");
        $MyData->setPalette($NombreCarrera." ".$P_Periodo."-".$Ac_Periodo,array("R"=>44,"G"=>87,"B"=>0));
        //$MyData->setPalette("Desercion Pregrado".$CodigoPeriodo."-".$Periodo_Actual);
        /******************************************/
        /* Create the pChart object */
            $numCarreras = 1;
            if($numCarreras<=30){
                $ancho = 900;
            } else if($numCarreras<=50) {
                //toca hacerlo mas largo porque son muchas carreras
                $ancho = 1200;
            } else if($numCarreras<=70) {
                $ancho = 1400;
            } else {
               $ancho = 1600;
            }
        /************************************/
        
        $alto = 800;
        $myPicture = new pImage($ancho,$alto,$MyData); 
        
        /* Turn of Antialiasing */
        $myPicture->Antialias = FALSE;
        
        /* Draw the background */
        
        /* Overlay with a gradient *///array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>80)
        $myPicture->drawGradientArea(0,0,$ancho,20,DIRECTION_VERTICAL,array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107));
        
        /* Add a border to the picture */
        $myPicture->drawRectangle(0,0,$ancho-1,$alto-1,array("R"=>0,"G"=>0,"B"=>0));
        
        /* Write the chart title */ 
        $myPicture->setFontProperties(array("FontName"=>$fontPath."verdana.ttf","FontSize"=>8,"R"=>255,"G"=>255,"B"=>255));
        
        $myPicture->drawText(10,16,$Typo_text." Semestral Institucional vs ".$Typo_text." Nacional",array("FontSize"=>7,"Align"=>TEXT_ALIGN_BOTTOMLEFT));
        
        /* Set the default font */
        $myPicture->setFontProperties(array("FontName"=>$fontPath."calibri.ttf","FontSize"=>8,"R"=>0,"G"=>0,"B"=>0));
        
        /* Define the chart area */
        //el 80 es donde empieza la grafica de izquierda a derecha y el 40 es de arriba para abajo
        //$myPicture->setGraphArea(60,40,$ancho-30,280);
        /*
        (60,40,650,200)
        
        60->Posicion del ajuste a la  izquierda del usuario
        
        */
        $myPicture->setGraphArea(60,80,850,600);
        
        /* Draw the scale */
        //$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"Mode"=>SCALE_MODE_START0,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
        
        $AxisBoundaries = array(0=>array("Min"=>0,"Max"=>$Max));
        $scaleSettings  = array("GridR"=>10,"GridG"=>10,"GridB"=>10,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE,"Mode"=>SCALE_MODE_MANUAL, "ManualScale"=>$AxisBoundaries);
        $myPicture->drawScale($scaleSettings); 
        /*$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"Mode"=>SCALE_MODE_ADDALL_START0,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
        $myPicture->drawScale($scaleSettings);*/ 
        
        /* Turn on Antialiasing */
        $myPicture->Antialias = TRUE;
        
        /* Enable shadow computing */
        $myPicture->setShadow(FALSE);
        
        /* Draw the line chart */
        $myPicture->drawLineChart(array("DisplayValues"=>FALSE,"DisplayColor"=>DISPLAY_AUTO));
        $myPicture->drawPlotChart(array("DisplayValues"=>TRUE,"PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80));
        
        /* Write the chart legend */
        //este es el nombre de arriba que de largo le puse 590 para que me quedaran los periodos en la punta  $ancho-320,9  //540,20
        $myPicture->drawLegend(320,9,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL,"FontR"=>255,"FontG"=>255,"FontB"=>255));
        /*******************************************************************************************************/
 

 /* Render the picture (choose the best way) */
        $myPicture->Render("GraficasObservatorio/GraficasDesercionSemestral/DesercionSemestral".$CodigoCarrera.".png");
        //$myPicture->autoOutput("GraficasObservatorio/GraficasDesercionSemestral/DesercionSemestral".$CodigoCarrera.".png"); 
 /**********************************************************************************************/
 
 /************************************************************************************************/
       ?>
       <img alt="Resultados Desrcion" src="<?php echo "GraficasObservatorio/GraficasDesercionSemestral/DesercionSemestral".$CodigoCarrera.".png?random=".time(); ?>" style="border: 1px solid gray;margin-right: 20px;"/>
       
       </div>
       
       </body>
   </html> 
<?PHP 

function MainJson(){
	
	global $db,$userid;
	
	include ('../templates/mainjson.php');
	
	
	$SQL_User='SELECT idusuario as id FROM usuario WHERE usuario="'.$_SESSION['MM_Username'].'"';
	
	if($Usario_id=&$db->Execute($SQL_User)===false){
		echo 'Error en el SQL Userid...<br>';
		die;
	}
	
	$userid=$Usario_id->fields['id'];
	
}


?>