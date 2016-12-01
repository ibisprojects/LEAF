<?php
//**************************************************************************************
// FileName: About.php
// Author: GN, RS, BF, NK
// The main Index page for the LEAF website (Living Atlas of East African Flora)
//**************************************************************************************

//**************************************************************************************
// Includes
//**************************************************************************************

require_once("C:/Inetpub/wwwroot/cwis438/Classes/Formatter.php");

require_once("C:/Inetpub/wwwroot/Classes/DBConnection.php");
require_once("C:/Inetpub/wwwroot/Classes/DBTable/TBL_DBTables.php");
require_once("C:/Inetpub/wwwroot/utilities/WebUtil.php");
require_once("C:/Inetpub/wwwroot/cwis438/classes/DBTable/TBL_Projects.php");
require_once("C:/Inetpub/wwwroot/cwis438/utilities/SecurityUtil.php");

//**************************************************************************************
// Security
//**************************************************************************************

$Database=NewConnection(INVASIVE_DATABASE);

//**************************************************************************************
// HTML Header block, client-side includes, and client-side functions
//**************************************************************************************

$ThePage=new PageSettings();

$ThePage->HeaderStart("Living Atlas of East African Flora");

?>

<!-- JQUERY Accordion -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.9.1.js"></script>
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<link rel="stylesheet" href="/resources/demos/style.css">

<script>
    
$(function() 
{
    $( "#accordion" ).accordion();
});

</script>

<style>
p
{
    font-size:16px;
}
</style>

<?php

$ThePage->HeaderEnd();

//**************************************************************************************
// HTML Generation
//**************************************************************************************

$ThePage->BodyStart();

$ThePage->LineBreak();

$ThePage->Heading(0,"Staff Profiles");

$ThePage->LineBreak();

echo("<div id='accordion'>");

    echo("<h3>Gregory Newman</h3>");
    echo("<div>");
        echo("<p>
            
            <img src='http://www.citsci.org/WebContent/WS/CitSci/Staff/Gregory_Newman.jpg' alt='Greg' style='float:right; margin-left:20px;' />

            Dr. Newman is a research scientist, ecologist, and informatics specialist at the Natural Resource Ecology Laboratory (NREL) 
            at Colorado State University (CSU). He received his PhD from CSU in citizen science, community-based monitoring, and ecological informatics. 
            His current research focuses on designing and evaluating the effectiveness of cyber-infrastructure support systems for citizen science programs. 
            His research interests include evaluating various citizen science program models, understanding the socio-ecological benefits of engaging the 
            public in scientific research, designing and evaluating data management systems for socio-ecological research, assessing the value of local and 
            traditional ecological knowledge for conservation and education outcomes, and developing spatial-temporal decision support systems.<br/><br/>

            Greg strives to create innovative ecological data management and visualization solutions to help communities solve place-based environmental 
            challenges. His research team at NREL manages the International Biological Information System (IBIS) cyber-infrastructure at NREL, a system 
            that supports the CitSci.org web/mobile applications and 20+ other ecological data management and web applications. He is currently a member 
            of the DataONE citizen science working group and the North American Pika Consortium IT subcommittee.
        </p>");
    echo("</div>");
    
    echo("<h3>Russell Scarpino</h3>");
        echo("<div>");
        echo("<p>
                <img src='http://www.citsci.org/WebContent/WS/CitSci/Staff/Russell_Scarpino3.jpg' alt='Russell' style='float:right; margin-left:20px;' />
                
                Russell is a Research Associate specializing in eco-informatics at the Natural Resource Ecology Laboratory (NREL) at Colorado State University.
                He has teamed up with Dr. Gregory Newman to manage the growing list of ecological data management and web applications of the International Biological 
                Information System (IBIS).<br><br>
                
                Mr. Scarpino&#39;s background is in Ecology, with an emphasis on Marine Biology and Conservation, and has since turned to the 
                dark side of programming and data management.  He is an advocate of community-based monitoring, open data, and open code.
            </p>");
        echo("</div>");
        
    echo("<h3>Nicole Kaplan</h3>");
        echo("<div>");
            echo("<p>
                
                <img src='http://www.citsci.org/WebContent/WS/CitSci/Staff/Nicole_Kaplan.jpg' alt='Nicole' style='float:right; margin:0px 0px 20px 20px;' />

                Nicole Kaplan is an Information Manager at the Natural Resource Ecology Lab. She has been providing scientific 
                support services since 1998, when she started working with the Shortgrass Steppe, Long Term Ecological Research 
                (LTER) project. Nicole works closely with researchers, graduate students or anyone providing data values or 
                observations of the natural world, to design and build data management systems. She has co-chaired the LTER Network 
                Information Management Committee (2003-2006), co-founded the Information Management Governance Working Group (2008) 
                where she participated in establishing priorities and procedures to develop and enact standards and best practices 
                that support network science, education, and information management policy for the US LTER Network.<br/><br/>
                
                Currently she serves on the Colorado State University Research Data Advisory Board (2013), charged with assessing 
                the data management needs of research teams and their stakeholders. 
            </p>");
        echo("</div>");
        
    echo("<h3>Nancy Sturtevant</h3>");
        echo("<div>");
            echo("<p>
                
                <img src='' alt='Nancy' style='float:right; margin:0px 0px 20px 20px;' />

                Nancy's information coming soon. 
            </p>");
        echo("</div>");    
        
    echo("<h3>Brian Fauver</h3>");
        echo("<div>");
        echo("<p>            
                <img src='http://www.citsci.org/WebContent/WS/CitSci/Staff/Brian_Fauver.png' alt='Brian' style='float:right; margin-left:20px;' />
                
                Brian is a graduate student in the Human Dimensions of Natural Resources at Colorado State University. 
                His thesis is studying an economic framework for citizen science program design. He has a background in citizen-science 
                coordination through working with the Front Range Pika Patrol (www.pikapatrol.com) doing pika detectability surveys, 
                and the Southwest Crown of the Continent CFLR Program (www.swcrown.org) doing vegetation transects. Both projects gave 
                him a firm understanding of the needs and limitations of small, locally based, citizen-science projects.<br/><br/>
                
                Simply put, he likes rigorous science, but he loves working with volunteers.
            </p>");
        echo("</div>");
        
echo("</div>");  // End accordion div


$ThePage->LineBreak();

$ThePage->BodyEnd();

?>
