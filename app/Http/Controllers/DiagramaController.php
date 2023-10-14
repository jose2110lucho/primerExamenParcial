<?php

namespace App\Http\Controllers;

use App\Events\DiagramaSent;
use App\Models\Diagrama;
use App\Models\Proyecto;
use App\Models\User;
use App\Models\User_diagrama;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Isset_;
use SimpleXMLElement;

class DiagramaController extends Controller
{
    public function index(Proyecto $proyecto)
    {
        $diagramas = $proyecto->diagramas()->paginate(4);
        return view('diagramas.index', compact('diagramas', 'proyecto'));
    }

    public function misDiagramas()
    {
        $diagramas = Auth::user()->misDiagramas()->paginate(3);
        return view('diagramas.misdiagramas', compact('diagramas'));
    }

    public function diagramar(Diagrama $diagrama)
    {
        $proyecto = $diagrama->proyecto;
        $permiso = Auth::user()->user_diagramas()->where('diagrama_id', $diagrama->id)->first();
        $permiso = $permiso->editar;
        return view('diagramas.diagramar', compact('diagrama', 'proyecto', 'permiso'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required'],
            'descripcion' => ['required'],
            'tipo' => ['required'],
        ]);
        try {
            $diagrama = new Diagrama();
            $diagrama->nombre = $request->nombre;
            $diagrama->descripcion = $request->descripcion;
            $diagrama->tipo = $request->tipo;
            $diagrama->user_id = Auth::user()->id;
            $diagrama->proyecto_id = $request->proyecto_id;
            if ($request->diagrama_id != 'nuevo') {
                $newDiagram = Diagrama::find($request->diagrama_id);
                $diagrama->contenido = $newDiagram->contenido;
            } else {
                $diagrama->contenido = '';
            }
            $diagrama->save();
            DB::table('user_diagramas')->insert([
                'user_id' => $diagrama->user_id,
                'diagrama_id' => $diagrama->id
            ]);
            return redirect()->route('diagramas.index', $request->proyecto_id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ha ocurrido un error' . $e->getMessage());
        }
    }

    public function editor(Request $request)
    {
        $user = User::find($request->input('id'));
        $relacion = $user->user_diagramas()->where('diagrama_id', $request->input('diagrama'))->first();
        $relacionv = User_diagrama::find($relacion->id);
        $relacionv->editar = $relacionv->editar == 0 ? 1 : 0;
        $relacionv->update();
        return response()->json(['mensaje' => 'Usuario desactivado...'], 200);
    }

    public function favorito(Request $request)
    {
        $diagrama = Diagrama::findOrFail($request->input('id'));
        $diagrama->favorito = $diagrama->favorito == 0 ? 1 : 0;
        $diagrama->update();
        return response()->json(['mensaje' => 'Usuario desactivado...'], 200);
        /* return  redirect()->back()->with('message', 'Se reitro de favoritos '); */
    }

    public function terminado(Request $request)
    {
        $diagrama = Diagrama::findOrFail($request->input('id'));
        $diagrama->terminado = $diagrama->terminado == 0 ? 1 : 0;
        $diagrama->update();
        return response()->json(['mensaje' => 'Usuario desactivado...'], 200);
        /* return  redirect()->back()->with('message', 'Se reitro de favoritos '); */
    }

    public function guardar(Request $request)
    {
        $diagrama = Diagrama::findOrFail($request->input('id'));
        $diagrama->contenido = $request->input('contenido');
        $diagrama->update();
        broadcast(new DiagramaSent($diagrama))->toOthers();
        return response()->json(['msm' => 'msmsms'], 200);
    }

    public function edit(Diagrama $diagrama)
    {
        return view('diagramas.edit', compact('diagrama'));
    }

    public function update(Request $request, Diagrama $diagrama)
    {
        try {
            $diagrama->nombre = $request->nombre;
            $diagrama->descripcion = $request->descripcion;
            $diagrama->tipo = $request->tipo;
            /* dd($request->url); */
            /* dd($diagrama->contenido); */
            $fp = fopen($request->url, "r");
            $text = "";
            $linea = "";
            while (!feof($fp)) {
                $diagrama->contenido = fgets($fp);
            }
            $diagrama->update();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ha ocurrido un error' . $e->getMessage());
        }
        return redirect()->route('diagramas.index', $diagrama->proyecto_id)->with('message', 'Se edito la inf del diagrama de manera correcta');
    }

    public function usuarios(Diagrama $diagrama)
    {
        $usuarios = $diagrama->proyecto->usuarios;
        return view('diagramas.usuarios', compact('diagrama', 'usuarios'));
    }

    public function agregar(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                DB::table('user_diagramas')->insert([
                    'user_id' => $request->user_id,
                    'diagrama_id' => $request->diagrama_id
                ]);
            });
            DB::commit();
            return redirect()->back()->with('message', 'Se agrego el usuario correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ha ocurrido un error' . $e->getMessage());
        }
    }

    public function banear(Request $request, Diagrama $diagrama)
    {
        try {
            $user = User::find($request->user_id);
            $relacion = Auth::user()->user_diagramas()->where('diagrama_id', $diagrama->id)->first();
            $rel = User_diagrama::find($relacion->id);
            $rel->delete();
            return redirect()->back()->with('message', 'Se removio al usuario del diagrama: ' . $diagrama->nombre);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ha ocurrido un error' . $e->getMessage());
        }
    }

    public function descargar(Diagrama $diagrama)
    {
        $contenido = $diagrama->contenido;
        $path = 'copia.c4';
        $th = fopen("copia.c4", "w");
        fclose($th);
        $ar = fopen("copia.c4", "a") or die("Error al crear");
        fwrite($ar, $contenido);
        fclose($ar);
        return response()->download($path);
    }

    public function exportar(Request $request)
    {
        $diagrama = Diagrama::find($request->diagrama_id);
        $fp = fopen($request->url, "r");
        $text = "";
        $xml = "";
        
        while (!feof($fp)) {
            $xml .= fgets($fp);
        }
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $array = json_decode($json, true);
        $array = $array["Table"];
        $objetos = []; 
        $atributos = [];
        $operaciones = [];
        $posiciones = [];
        $enlaces = [];
        for ($i=0; $i < count($array); $i++) { 
            if( $array[$i]["@attributes"]["name"] == "t_object" ){
                $objetos = $array[$i]["Row"];
            }elseif($array[$i]["@attributes"]["name"] == "t_diagramobjects"){
                $posiciones = $array[$i]["Row"];
            }elseif($array[$i]["@attributes"]["name"] == "t_connector"){
                $enlaces = $array[$i]["Row"];
            }elseif($array[$i]["@attributes"]["name"] == "t_attribute"){
                $atributos = $array[$i]["Row"];
            }elseif($array[$i]["@attributes"]["name"] == "t_operation"){
                $operaciones = $array[$i]["Row"];
            }
        }
        $objetosText = '';
        $f = 0;
        for ($i=1; $i < count($objetos); $i++) { 
            if($objetos[$i]["Column"][1]["@attributes"]["value"] == 'Class'){
                $objetosText .= Diagrama::objeto($objetos[$i]["Column"], $posiciones[$i-1]["Column"], $atributos[$f]["Column"], $operaciones[$f]["Column"]);
                $f += 1;
            }else{
                $objetosText .= Diagrama::boundary($objetos[$i]["Column"], $posiciones[$i-1]["Column"]);
            }
        }
        
        $enlacesText = "";
        if(!array_key_exists("Column", $enlaces)){
            for ($i=0; $i < count($enlaces) ; $i++) { 
                $enlacesText .= Diagrama::enlace($enlaces[$i]);
            }
        }elseif(array_key_exists("Column", $enlaces)){
            $enlacesText .= Diagrama::enlace($enlaces);
        }
        // dd($enlacesText);
        // dd($objetosText);
        // dd($array);

        $enlacesText = substr($enlacesText, 0, strlen($enlacesText)-1);
        $contenido = '{"cells": ['.$objetosText.$enlacesText.']}';
        $diagrama->contenido = trim(preg_replace('/\s+/', ' ', $contenido));
        $diagrama->update();
        return redirect()->back();
    }

    public function architect(Request $request)
    {
        $objeto = Diagrama::find($request->diagrama_id);
        $diagrama = json_decode($objeto->contenido);
        /* dd($objeto, $diagrama); */

        $diagrama = $diagrama->cells;
        /* return $diagrama; */

        $objetos = '';
        $atributos = '';
        $operations = '';
        $positions = '';
        $conecciones = '';
        $extensiones = '';
        for ($i = 0; $i < count($diagrama); $i++) {
            if ($diagrama[$i]->type == 'standard.EmbeddedImage' || $diagrama[$i]->type == 'standard.Rectangle' && $diagrama[$i]->attrs->contentText->text != "") {
                $objetos .= $this->objeto($diagrama[$i], $i + 1);
                $positions .= $this->position($diagrama[$i], $i + 1);
                $atributos .= $this->attribute($diagrama[$i], $i + 1);
                $operations .= $this->operation($diagrama[$i], $i + 1);
            } elseif ($diagrama[$i]->type == 'app.Link') {
                /* dd($diagrama[$i]); */
                /* if($diagrama[$i]->source->id && $diagrama[$i]->target->id ){
                    $conecciones .= $this->conector($diagrama[$i], $i + 1);
                    $extensiones .= $this->extension($diagrama[$i], $i + 1);
                } */
                if (property_exists($diagrama[$i]->source, 'id') && property_exists($diagrama[$i]->target, 'id')) {
                    $conecciones .= $this->conector($diagrama[$i], $i + 1);
                    $extensiones .= $this->extension($diagrama[$i], $i + 1);
                }
                
            } else if ($diagrama[$i]->attrs->contentText->text == "") {
                $objetos .= $this->boundary($diagrama[$i], $i + 1);
                $positions .= $this->position($diagrama[$i], $i + 1);
            }
        }

        $xml = '<?xml version="1.0" encoding="windows-1252"?>
        <Package name="Package1" guid="{EC8004E0-A94E-4205-9A63-6F87F6D67F1F}">
            <Table name="t_package">
                <Row>
                    <Column name="Package_ID" value="3" />
                    <Column name="Name" value="Package1" />
                    <Column name="Parent_ID" value="1" />
                    <Column name="CreatedDate" value="2022-11-20 16:33:48" />
                    <Column name="ModifiedDate" value="2022-11-20 16:33:48" />
                    <Column name="ea_guid" value="{EC8004E0-A94E-4205-9A63-6F87F6D67F1F}" />
                    <Column name="IsControlled" value="FALSE" />
                    <Column name="LastLoadDate" value="2022-11-20 16:50:30" />
                    <Column name="LastSaveDate" value="2022-11-20 16:50:30" />
                    <Column name="Version" value="1.0" />
                    <Column name="Protected" value="FALSE" />
                    <Column name="UseDTD" value="FALSE" />
                    <Column name="LogXML" value="FALSE" />
                    <Column name="TPos" value="0" />
                    <Column name="PackageFlags" value="isModel=1;VICON=3;" />
                    <Column name="BatchSave" value="0" />
                    <Column name="BatchLoad" value="0" />
                    <Extension />
                </Row>
            </Table>
            <Table name="t_object">
                <Row>
                    <Column name="Object_ID" value="1" />
                    <Column name="Object_Type" value="Package" />
                    <Column name="Diagram_ID" value="0" />
                    <Column name="Name" value="Package1" />
                    <Column name="Author" value="pedri" />
                    <Column name="Version" value="1.0" />
                    <Column name="Package_ID" value="1" />
                    <Column name="NType" value="0" />
                    <Column name="Complexity" value="1" />
                    <Column name="Effort" value="0" />
                    <Column name="Backcolor" value="-1" />
                    <Column name="BorderStyle" value="0" />
                    <Column name="BorderWidth" value="-1" />
                    <Column name="Fontcolor" value="-1" />
                    <Column name="Bordercolor" value="-1" />
                    <Column name="CreatedDate" value="2022-11-20 16:33:48" />
                    <Column name="ModifiedDate" value="2022-11-20 16:33:48" />
                    <Column name="Status" value="Proposed" />
                    <Column name="Abstract" value="0" />
                    <Column name="Tagged" value="0" />
                    <Column name="PDATA1" value="3" />
                    <Column name="GenType" value="Java" />
                    <Column name="Phase" value="1.0" />
                    <Column name="Scope" value="Public" />
                    <Column name="Classifier" value="0" />
                    <Column name="ea_guid" value="{EC8004E0-A94E-4205-9A63-6F87F6D67F1F}" />
                    <Column name="ParentID" value="0" />
                    <Column name="TPos" value="0" />
                    <Column name="IsRoot" value="FALSE" />
                    <Column name="IsLeaf" value="FALSE" />
                    <Column name="IsSpec" value="FALSE" />
                    <Column name="IsActive" value="FALSE" />
                    <Extension PDATA1="{EC8004E0-A94E-4205-9A63-6F87F6D67F1F}" />
                </Row>
                ' . $objetos . '
            </Table>
            <Table name="t_attribute">
                ' . $atributos . '
            </Table>
            <Table name="t_operation">
            ' . $operations . '
            </Table>
            <Table name="t_connector">
            ' . $conecciones . '
            </Table>
            <Table name="t_diagram">
                <Row>
                    <Column name="Diagram_ID" value="12" />
                    <Column name="Package_ID" value="3" />
                    <Column name="ParentID" value="0" />
                    <Column name="Diagram_Type" value="Logical" />
                    <Column name="Name" value="Package1" />
                    <Column name="Version" value="1.0" />
                    <Column name="Author" value="pedri" />
                    <Column name="ShowDetails" value="0" />
                    <Column name="AttPub" value="TRUE" />
                    <Column name="AttPri" value="TRUE" />
                    <Column name="AttPro" value="TRUE" />
                    <Column name="Orientation" value="P" />
                    <Column name="cx" value="850" />
                    <Column name="cy" value="1098" />
                    <Column name="Scale" value="100" />
                    <Column name="CreatedDate" value="2022-12-04 18:19:10" />
                    <Column name="ModifiedDate" value="2022-12-04 18:49:24" />
                    <Column name="ShowForeign" value="TRUE" />
                    <Column name="ShowBorder" value="TRUE" />
                    <Column name="ShowPackageContents" value="TRUE" />
                    <Column name="PDATA" value="HideRel=0;ShowTags=0;ShowReqs=0;ShowCons=0;OpParams=1;ShowSN=0;ScalePI=0;PPgs.cx=0;PPgs.cy=0;PSize=1;ShowIcons=1;SuppCN=0;HideProps=0;HideParents=0;UseAlias=0;HideAtts=0;HideOps=0;HideStereo=0;HideEStereo=0;ShowRec=1;ShowRes=0;ShowShape=1;FormName=;" />
                    <Column name="Locked" value="FALSE" />
                    <Column name="ea_guid" value="{54BA53B2-4E2C-4c7c-8FFC-F0897ABF1B34}" />
                    <Column name="Swimlanes" value="locked=false;orientation=0;width=0;inbar=false;names=false;color=-1;bold=false;fcol=0;tcol=-1;ofCol=-1;ufCol=-1;hl=1;ufh=0;hh=0;cls=0;bw=0;hli=0;" />
                    <Column name="StyleEx" value="ExcludeRTF=0;DocAll=0;HideQuals=0;AttPkg=1;ShowTests=0;ShowMaint=0;SuppressFOC=1;MatrixActive=0;SwimlanesActive=1;KanbanActive=0;MatrixLineWidth=1;MatrixLineClr=0;MatrixLocked=0;TConnectorNotation=UML 2.1;TExplicitNavigability=0;AdvancedElementProps=1;AdvancedFeatureProps=1;AdvancedConnectorProps=1;m_bElementClassifier=1;SPT=1;MDGDgm=Code Engineering::PHP;DefaultLang=PHP;STBLDgm=;ShowNotes=0;VisibleAttributeDetail=0;ShowOpRetType=1;SuppressBrackets=0;SuppConnectorLabels=0;PrintPageHeadFoot=0;ShowAsList=0;SuppressedCompartments=;Theme=:119;SaveTag=FBC407A9;" />
                    <Extension Package_ID="{EC8004E0-A94E-4205-9A63-6F87F6D67F1F}" />
                </Row>
            </Table>
            <Table name="t_diagramobjects">
            ' . $positions . '
            </Table>
            <Table name="t_diagramlinks">
            ' . $extensiones . '
            </Table>
        </Package>';

        $path = 'model.xml';
        $ar = fopen("model.xml", "w") or die("Error al crear");
        fwrite($ar, $xml);
        fclose($ar);
        return response()->download($path);
        /* ["Table"][0]["Row"]["Column"] */ /* ->Table[0]->attributes->name */
    }



    //demo

    public function architect1(Request $request)
    {
        
        $objeto = Diagrama::find($request->diagrama_id);
        $diagrama = json_decode($objeto->contenido);
        /* dd($objeto, $diagrama); */

        $diagrama = $diagrama->cells;

        $data = $diagrama;
        
        

        $xml = new DOMDocument();
        $xml->loadXML('<model><elements></elements></model>');

        foreach ($data as $key => $item) {
            $element = $xml->createElement('element');
            $element->setAttribute('type', $item->type);

            $position = $xml->createElement('position');

            if(isset($item->position)){
                $position->setAttribute('x', $item->position->x);
                $position->setAttribute('y', $item->position->y);
                $element->appendChild($position);
            }
            
            if(isset($item->size)){
                $size = $xml->createElement('size');
                $size->setAttribute('width', $item->size->width);
                $size->setAttribute('height', $item->size->height);
                $element->appendChild($size);
            }
            
            if(isset($item->angle)){
                $angle = $xml->createElement('angle', $item->angle);
                $element->appendChild($angle);
            }
            
            if(isset($item->id)){
                $id = $xml->createElement('id', $item->id);
                $element->appendChild($id);
            }
            
            if(isset($item->z)){
                $z = $xml->createElement('z', $item->z);
                $element->appendChild($z);
            }
            

            if (isset($item->attrs)) {
                foreach ($item->attrs as $key => $value) {
                    $attr = $xml->createElement($key);
                    foreach ($value as $k => $v) {
                        $attr->setAttribute($k, json_encode($v));
                    }
                    $element->appendChild($attr);
                }
            }

            $xml->documentElement->appendChild($element);
        }
        
        $xml->save('xml.xml');
        dd($xml);
        
    }

    public function objeto($diagrama, $i)
    {
        $objeto = '<Row>
        <Column name="Object_ID" value="' . $i . '" />
        <Column name="Object_Type" value="Class" />
        <Column name="Diagram_ID" value="0" />
        <Column name="Name" value="' . $diagrama->attrs->headerText->text . '" />
        <Column name="Author" value="pedri" />
        <Column name="Version" value="1.0" />
        <Column name="Package_ID" value="3" />
        <Column name="NType" value="0" />
        <Column name="Complexity" value="1" />
        <Column name="Effort" value="0" />
        <Column name="Backcolor" value="-1" />
        <Column name="BorderStyle" value="0" />
        <Column name="BorderWidth" value="-1" />
        <Column name="Fontcolor" value="-1" />
        <Column name="Bordercolor" value="-1" />
        <Column name="CreatedDate" value="2022-12-04 18:19:13" />
        <Column name="ModifiedDate" value="2022-12-04 18:31:11" />
        <Column name="Status" value="Proposed" />
        <Column name="Abstract" value="0" />
        <Column name="Tagged" value="0" />
        <Column name="PDATA4" value="0" />
        <Column name="GenType" value="PHP" />
        <Column name="Phase" value="1.0" />
        <Column name="Scope" value="Public" />
        <Column name="Classifier" value="0" />
        <Column name="ea_guid" value="{' . $diagrama->id . '}" />
        <Column name="ParentID" value="0" />
        <Column name="IsRoot" value="FALSE" />
        <Column name="IsLeaf" value="FALSE" />
        <Column name="IsSpec" value="FALSE" />
        <Column name="IsActive" value="FALSE" />
        <Extension Package_ID="{EC8004E0-A94E-4205-9A63-6F87F6D67F1F}" />
        </Row>';
        return $objeto;
    }

    public function attribute($diagrama, $i)
    {
        $atributo = '<Row>
        <Column name="Object_ID" value="' . $i . '" />
        <Column name="Name" value="' . $diagrama->attrs->subHeaderText->text . '" />
        <Column name="Scope" value="Public" />
        <Column name="Containment" value="Not Specified" />
        <Column name="IsStatic" value="0" />
        <Column name="IsCollection" value="0" />
        <Column name="IsOrdered" value="0" />
        <Column name="AllowDuplicates" value="0" />
        <Column name="LowerBound" value="1" />
        <Column name="UpperBound" value="1" />
        <Column name="ID" value="' . $i . '" />
        <Column name="Pos" value="0" />
        <Column name="Classifier" value="0" />
        <Column name="ea_guid" value="{' . $diagrama->id . '}" />
        <Column name="StyleEx" value="volatile=0;" />
        <Extension Object_ID="{' . $diagrama->id . '}" />
        </Row>';
        return $atributo;
    }

    public function operation($diagrama, $i)
    {
        $operative = '<Row>
        <Column name="OperationID" value="' . $i . '" />
        <Column name="Object_ID" value="' . $i . '" />
        <Column name="Name" value="' . $diagrama->attrs->contentText->text . '" />
        <Column name="Scope" value="Public" />
        <Column name="Concurrency" value="Sequential" />
        <Column name="Pos" value="0" />
        <Column name="Pure" value="FALSE" />
        <Column name="Classifier" value="0" />
        <Column name="IsRoot" value="FALSE" />
        <Column name="IsLeaf" value="FALSE" />
        <Column name="IsQuery" value="FALSE" />
        <Column name="ea_guid" value="{0B668E21-C82D-4fe2-A263-F76A0189C4DC}" />
        <Extension Object_ID="{' . $diagrama->id . '}" />
        </Row>';
        return $operative;
    }

    public function position($diagrama, $i)
    {
        $position = '<Row>
        <Column name="Diagram_ID" value="12" />
        <Column name="Object_ID" value="' . $i . '" />
        <Column name="RectTop" value="-' . ((int) ($diagrama->position->y)) . '" />
        <Column name="RectLeft" value="' . ((int) $diagrama->position->x) . '" />
        <Column name="RectRight" value="' . ((int) $diagrama->position->x + $diagrama->size->width + 10) . '" />
        <Column name="RectBottom" value="-' . ((int) ($diagrama->position->y + $diagrama->size->height + 10)) . '" />
        <Column name="Sequence" value="' . $i . '" />
        <Column name="ObjectStyle" value="DUID=4317C271;" />
        <Column name="Instance_ID" value="' . $i . '" />
        <Extension Diagram_ID="{54BA53B2-4E2C-4c7c-8FFC-F0897ABF1B34}" Object_ID="{' . $diagrama->id . '}" />
        </Row>';
        return $position;
    }

    public function conector($diagrama, $i)
    {
        $label = '';
        if (count($diagrama->labels) > 0) {
            $label = $diagrama->labels[0]->attrs->text->text;
        }
       /*  dd($diagrama->source->id, $diagrama->target->id); */
        /* dd($diagrama); */
        $coneccion = '<Row>
        <Column name="Connector_ID" value="' . $i . '" />
        <Column name="Name" value="' . $label . '" />
        <Column name="Direction" value="Source -&gt; Destination" />
        <Column name="Connector_Type" value="Dependency" />
        <Column name="SourceAccess" value="Public" />
        <Column name="DestAccess" value="Public" />
        <Column name="SourceContainment" value="Unspecified" />
        <Column name="SourceIsAggregate" value="0" />
        <Column name="SourceIsOrdered" value="0" />
        <Column name="DestContainment" value="Unspecified" />
        <Column name="DestIsAggregate" value="0" />
        <Column name="DestIsOrdered" value="0" />
        <Column name="Start_Object_ID" value="50" />
        <Column name="End_Object_ID" value="51" />
        <Column name="Start_Edge" value="0" />
        <Column name="End_Edge" value="0" />
        <Column name="PtStartX" value="0" />
        <Column name="PtStartY" value="0" />
        <Column name="PtEndX" value="0" />
        <Column name="PtEndY" value="0" />
        <Column name="SeqNo" value="0" />
        <Column name="HeadStyle" value="0" />
        <Column name="LineStyle" value="0" />
        <Column name="RouteStyle" value="3" />
        <Column name="IsBold" value="0" />
        <Column name="LineColor" value="-1" />
        <Column name="PDATA5" value="SX=0;SY=0;EX=0;EY=0;" />
        <Column name="DiagramID" value="0" />
        <Column name="ea_guid" value="{' . $diagrama->id . '}" />
        <Column name="SourceIsNavigable" value="FALSE" />
        <Column name="DestIsNavigable" value="TRUE" />
        <Column name="IsRoot" value="FALSE" />
        <Column name="IsLeaf" value="FALSE" />
        <Column name="IsSpec" value="FALSE" />
        <Column name="SourceChangeable" value="none" />
        <Column name="DestChangeable" value="none" />
        <Column name="SourceTS" value="instance" />
        <Column name="DestTS" value="instance" />
        <Column name="IsSignal" value="FALSE" />
        <Column name="IsStimulus" value="FALSE" />
        <Column name="Target2" value="-1263619272" />
        <Column name="SourceStyle" value="Union=0;Derived=0;AllowDuplicates=0;Owned=0;Navigable=Non-Navigable;" />
        <Column name="DestStyle" value="Union=0;Derived=0;AllowDuplicates=0;Owned=0;Navigable=Navigable;" />
        
        <Extension Start_Object_ID="{' . $diagrama->source->id . '}" End_Object_ID="{' . $diagrama->target->id . '}" />
        </Row>';
        return $coneccion;
    }

    public function extension($diagrama, $i)
    {
        $extension = '<Row>
        <Column name="DiagramID" value="12" />
        <Column name="ConnectorID" value="' . $i . '" />
        <Column name="Geometry" value="SX=0;SY=0;EX=0;EY=0;EDGE=2;$LLB=;LLT=;LMT=CX=20:CY=14:OX=0:OY=0:HDN=0:BLD=0:ITA=0:UND=0:CLR=-1:ALN=1:DIR=0:ROT=0;LMB=;LRT=;LRB=;IRHS=;ILHS=;" />
        <Column name="Style" value="Mode=3;EOID=45DF43AE;SOID=9C7A8367;Color=-1;LWidth=0;" />
        <Column name="Hidden" value="FALSE" />
        <Column name="Instance_ID" value="' . $i . '" />
        <Extension DiagramID="{54BA53B2-4E2C-4c7c-8FFC-F0897ABF1B34}" ConnectorID="{' . $diagrama->id . '}" />
        </Row>';
        return $extension;
    }

    public function boundary($diagrama, $i)
    {
        $boundary = '<Row>
        <Column name="Object_ID" value="' . $i . '" />
        <Column name="Object_Type" value="Boundary" />
        <Column name="Diagram_ID" value="0" />
        <Column name="Name" value="' . $diagrama->attrs->headerText->text . $diagrama->attrs->subHeaderText->text . '" />
        <Column name="Author" value="pedri" />
        <Column name="Version" value="1.0" />
        <Column name="Package_ID" value="3" />
        <Column name="NType" value="0" />
        <Column name="Complexity" value="1" />
        <Column name="Effort" value="0" />
        <Column name="Backcolor" value="-1" />
        <Column name="BorderStyle" value="2" />
        <Column name="BorderWidth" value="-1" />
        <Column name="Fontcolor" value="-1" />
        <Column name="Bordercolor" value="-1" />
        <Column name="CreatedDate" value="2022-12-06 11:51:38" />
        <Column name="ModifiedDate" value="2022-12-06 11:56:08" />
        <Column name="Status" value="Proposed" />
        <Column name="Abstract" value="0" />
        <Column name="Tagged" value="0" />
        <Column name="PDATA1" value="1" />
        <Column name="PDATA2" value="1" />
        <Column name="PDATA3" value="0" />
        <Column name="GenType" value="PHP" />
        <Column name="Phase" value="1.0" />
        <Column name="Scope" value="Public" />
        <Column name="Classifier" value="0" />
        <Column name="ea_guid" value="{' . $diagrama->id . '}" />
        <Column name="ParentID" value="0" />
        <Column name="IsRoot" value="FALSE" />
        <Column name="IsLeaf" value="FALSE" />
        <Column name="IsSpec" value="FALSE" />
        <Column name="IsActive" value="FALSE" />
        <Extension Package_ID="{EC8004E0-A94E-4205-9A63-6F87F6D67F1F}" />
        </Row>';
        return $boundary;
    }

    public function script(Diagrama $diagrama)
    {
        $nombre = $diagrama->nombre;
        $contenido = json_decode($diagrama->contenido);
       
        $cell = $contenido->cells;                
        //dd($cell);
        $sql = 'create database ' .$nombre. ';'.PHP_EOL.' use ' .$nombre. ';'.PHP_EOL.PHP_EOL;

        
        for ($i = 0; $i < count($cell); $i++) {
            $primary = '';
            $c = 0;
            if ($cell[$i]->type == 'uml.Class' ) {
                if(count($cell[$i]->attributes) != 0){
                    $sql .= 'create table '. $cell[$i]->name. '( '.PHP_EOL;
                    
                    $atri = $cell[$i]->attributes;
                    for ($j = 0; $j < count($atri); $j++) {   

                        if(str_contains($atri[$j], 'Pk')|| str_contains($atri[$j], 'PK')){
                            if($c == 0){
                                $pieces = explode(" ", $atri[$j]);
                                $primary = $pieces[0] ;
                                $c++;
                            }else{
                                $pieces = explode(" ", $atri[$j]);
                                $primary .= ', '.$pieces[0] ;
                            }
                                
                        }   

                        if(str_contains($atri[$j], 'Fk')|| str_contains($atri[$j], 'FK')|| str_contains($atri[$j], 'fk')){
                            if($j == count($atri)-1){
                                $pieces = explode(" ", $atri[$j]);
                                if(str_contains($pieces[0], '_')){
                                    $foranea = explode("_", $pieces[0]);
                                    $sql .= ' '.$pieces[0]. ' ' .$pieces[1].', '.PHP_EOL.'primary key(' .$primary.'), '.PHP_EOL.' FOREIGN KEY ('.$pieces[0].') REFERENCES '.$foranea[1].'('.$foranea[0].') ON DELETE CASCADE  ON UPDATE CASCADE);'.PHP_EOL;
                                }else{
                                    $sql .= 'foranea mal definida'.PHP_EOL;
                                }
                            }else{
                                $pieces = explode(" ", $atri[$j]);
                                if(str_contains($pieces[0], '_')){
                                    $foranea = explode("_", $pieces[0]);
                                    $sql .= ' ' .$pieces[0].' ' .$pieces[1]. ','.PHP_EOL.' FOREIGN KEY ('.$pieces[0].') REFERENCES '.$foranea[1].'('.$foranea[0].') ON DELETE CASCADE ON UPDATE CASCADE);'.PHP_EOL;
                                }else{
                                    $sql .= 'foranea mal definida'.PHP_EOL;
                                }    
                            }

                        }else{
                            if($j == count($atri)-1){
                                $pieces = explode(" ", $atri[$j]);
                                $sql .= ' '.$pieces[0]. ' ' .$pieces[1].', '.PHP_EOL.' primary key(' .$primary.') '.PHP_EOL.' );'.PHP_EOL;
                            }else{
                                $pieces = explode(" ", $atri[$j]);
                                $sql .= ' ' .$pieces[0].' ' .$pieces[1]. ','.PHP_EOL;
                            }
                        }            
                    }
                }
            } 
        }

        $path = 'script.sql';
        $th = fopen("script.sql", "w");
        fclose($th);
        $ar = fopen("script.sql", "a") or die("Error al crear");
        fwrite($ar, $sql);
        fclose($ar);
        return response()->download($path);
    }



    public function generarJavaCode(Diagrama $diagrama){
        $nombre = $diagrama->nombre;
        $contenido = json_decode($diagrama->contenido); //arroja en formato json la info de las componentes graficas del diagrama
        
        /* dd($contenido); */

        $elementos = $contenido->cells; //especificamos la var elementos como la var que accedera a la info de los componentes
        /* dd($elementos); */
          
        $clases = []; //declaramos un array para guardar el nombre de todas las clases que se generen del diagrama
        $metodos = []; //declaramos un array para guardar el nombre de todas los metodos que se generen del diagrama

        
        foreach ($elementos as $element) { // vamos a recorrer el array de objetos que estan en formato json (componentes del diagrama)
            if($element->type == 'standard.EmbeddedImage' or $element->type == 'erd.Entity'){ // preguntamos si el elemento es de tipo actor, boundary, control o entity 

                if($element->type == 'standard.EmbeddedImage'){

                    $clases[$element->id] = $element->attrs->headerText->text; //vamos a guardar en el array de clases, el nombre de la clase en la posicion determinada por el id del objeto

                }else{
                    $clases[$element->id] = $element->attrs->text->text;
                }
                /* dd($clases[$element->id]); */

            }else if($element->type == 'app.Link'){  //preguntamos si el elemento es del tipo flecha o conector
               
                if(isset($element->id)){  // preguntamos si la flecha o connector tiene un atributo id y este no es nulo
                  
                   if(count($element->labels) > 0){ // preguntamos si la felcha o connector tiene una etiqueta(esa etiqueta representa una funcion/metodo)

                        $cadenaMetodo = $element->labels[0]->attrs->text->text; //asignamos a una variable la cadena que representa a esa funcion/metodo

                        // Patrón de expresión regular para encontrar el nombre de la función y los parámetros
                        $patron = '/^(\w+)\(([^)]*)\)$/'; // validamos que la cadena tenga la forma: nombrefuncion(tipo parametro, tipo parametro)

                        // Realizar la coincidencia con la expresión regular
                        if (preg_match($patron, $cadenaMetodo, $coincidencias)) {  // preguntamos si la cadena que representa a la funcion/metodo cumple con el patron descrito 
                            // El nombre de la función se encuentra en $coincidencias[1]
                            $nombreFuncion = $coincidencias[1];

                            // Los parámetros se encuentran en $coincidencias[2]
                            $parametros = explode(',', $coincidencias[2]); // array de subcadenas llamado $parametros que guarda los parametros de la funcion/metodo

                            $parametros = array_map('trim', $parametros); // eliminamos los espacios en blanco al principio y al final de cada parametro de la funcion/metodo y  lo volvemos a cqrgar en el array $parametros

                            $parametrosFormateados = []; //declaramos una array vacio para guardar los parametros 'tipo parametro' con el formato de java

                            foreach ($parametros as $parametro) { // recorremos el array $parametros 
                                
                                $arrayParametro = explode(' ', $parametro); // creamos un array de subcadenas donde guardamos el tipo del parametro y el parametro

                                if( strtolower($arrayParametro[0]) == 'int'){ // convierte el primer elemento del arreglo en minusculas y compara

                                    $parametrosFormateados[] = "int {$arrayParametro[1]}"; // inserta en el array con la forma "int parametro"
                                     
                                }else if(strtolower($arrayParametro[0]) == 'string'){ // convierte el primer elemento del arreglo en minusculas y compara

                                    $parametrosFormateados[] = "String {$arrayParametro[1]}"; // inserta en el array con la forma "String parametro"

                                }else if(strtolower($arrayParametro[0]) == 'boolean'){ // convierte el primer elemento del arreglo en minusculas y compara

                                    $parametrosFormateados[] = "boolean {$arrayParametro[1]}"; // inserta en el array con la forma "boolean parametro"

                                }
                                
                            }

                            $var = implode(',', $parametrosFormateados); // aqui unimos en una sola cadena separada por comas los elementos del array $parametrosFormateados

                            $targetID = $element->target->id; //guarda el id de la flecha que coincide con el id de la casilla de activacion que contiene el nombre de la clase a la que pertenece el metodo que viaja en la flecha

                            $nombreDeClaseAquePertenece = "";

                            foreach ($elementos as $e) {
                                if($e->type == 'standard.Rectangle'){
                                    if($e->id == $targetID){
                                        $nombreDeClaseAquePertenece = $e->attrs->headerText->text; 
                                        break;
                                    }
                                }
                            }




                            $metodos[$nombreDeClaseAquePertenece][] = "public void {$nombreFuncion}({$var}){\n}";

                            /* dd($metodos); */
                            
                        } 
                    

                   } 


                   
                }
                
            }
            
        } 


        

        /* dd($metodos); */

        $nombresClases = array_values($clases);

        /* dd($nombresClases); */




        $formatoClase = [];

        foreach ($nombresClases as $clase) {

            $mayus = ucfirst($clase);
            if(isset($metodos[$clase])){
                $variable = implode("\n", $metodos[$clase]);
                $formatoClase[$mayus]  = "public class {$mayus} { \n $variable\n}" ;
            }else{
                $formatoClase[$mayus]  = "public class {$mayus} {\n}" ;
            }
            

        }

        /* dd($formatoClase); */


        //
            $directorioDestino = "C:/Users/Pepe/Downloads/aqui/";

            // Verifica si el directorio de destino existe y, si no, créalo
            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0777, true);
            }

            foreach ($formatoClase as $key => $class) {
                // Genera un nombre de archivo único para cada class
                $nombreArchivo = $directorioDestino.$key.".java";

                // Abre el archivo para escritura
                $archivo = fopen($nombreArchivo, 'w');

                if ($archivo) {
                    // Escribe el class en el archivo
                    fwrite($archivo, $class);
                    
                    // Cierra el archivo
                    fclose($archivo);
                    
                    
                } 
            }
            return redirect()->back()->with('message', 'se han generado exitosamente los archivos en JAVA');
        //

    }
    
    public function generarCCode(Diagrama $diagrama){
        $nombre = $diagrama->nombre;
        $contenido = json_decode($diagrama->contenido); //arroja en formato json la info de las componentes graficas del diagrama
        
        /* dd($contenido); */

        $elementos = $contenido->cells; //especificamos la var elementos como la var que accedera a la info de los componentes
        /* dd($elementos); */
          
        $clases = []; //declaramos un array para guardar el nombre de todas las clases que se generen del diagrama
        $metodos = []; //declaramos un array para guardar el nombre de todas los metodos que se generen del diagrama

        
        foreach ($elementos as $element) { // vamos a recorrer el array de objetos que estan en formato json (componentes del diagrama)
            if($element->type == 'standard.EmbeddedImage' or $element->type == 'erd.Entity'){ // preguntamos si el elemento es de tipo actor, boundary, control o entity 

                if($element->type == 'standard.EmbeddedImage'){

                    $clases[$element->id] = $element->attrs->headerText->text; //vamos a guardar en el array de clases, el nombre de la clase en la posicion determinada por el id del objeto

                }else{
                    $clases[$element->id] = $element->attrs->text->text;
                }
                /* dd($clases[$element->id]); */

            }else if($element->type == 'app.Link'){  //preguntamos si el elemento es del tipo flecha o conector
               
                if(isset($element->id)){  // preguntamos si la flecha o connector tiene un atributo id y este no es nulo
                  
                   if(count($element->labels) > 0){ // preguntamos si la felcha o connector tiene una etiqueta(esa etiqueta representa una funcion/metodo)

                        $cadenaMetodo = $element->labels[0]->attrs->text->text; //asignamos a una variable la cadena que representa a esa funcion/metodo

                        // Patrón de expresión regular para encontrar el nombre de la función y los parámetros
                        $patron = '/^(\w+)\(([^)]*)\)$/'; // validamos que la cadena tenga la forma: nombrefuncion(tipo parametro, tipo parametro)

                        // Realizar la coincidencia con la expresión regular
                        if (preg_match($patron, $cadenaMetodo, $coincidencias)) {  // preguntamos si la cadena que representa a la funcion/metodo cumple con el patron descrito 
                            // El nombre de la función se encuentra en $coincidencias[1]
                            $nombreFuncion = $coincidencias[1];

                            // Los parámetros se encuentran en $coincidencias[2]
                            $parametros = explode(',', $coincidencias[2]); // array de subcadenas llamado $parametros que guarda los parametros de la funcion/metodo

                            $parametros = array_map('trim', $parametros); // eliminamos los espacios en blanco al principio y al final de cada parametro de la funcion/metodo y  lo volvemos a cqrgar en el array $parametros

                            $parametrosFormateados = []; //declaramos una array vacio para guardar los parametros 'tipo parametro' con el formato de java

                            foreach ($parametros as $parametro) { // recorremos el array $parametros 
                                
                                $arrayParametro = explode(' ', $parametro); // creamos un array de subcadenas donde guardamos el tipo del parametro y el parametro

                                if( strtolower($arrayParametro[0]) == 'int'){ // convierte el primer elemento del arreglo en minusculas y compara

                                    $parametrosFormateados[] = "int {$arrayParametro[1]}"; // inserta en el array con la forma "int parametro"
                                     
                                }else if(strtolower($arrayParametro[0]) == 'string'){ // convierte el primer elemento del arreglo en minusculas y compara

                                    $parametrosFormateados[] = "string {$arrayParametro[1]}"; // inserta en el array con la forma "String parametro"

                                }else if(strtolower($arrayParametro[0]) == 'bool'){ // convierte el primer elemento del arreglo en minusculas y compara

                                    $parametrosFormateados[] = "bool {$arrayParametro[1]}"; // inserta en el array con la forma "boolean parametro"

                                }
                                
                            }

                            $var = implode(',', $parametrosFormateados); // aqui unimos en una sola cadena separada por comas los elementos del array $parametrosFormateados

                            $targetID = $element->target->id; //guarda el id de la flecha que coincide con el id de la casilla de activacion que contiene el nombre de la clase a la que pertenece el metodo que viaja en la flecha

                            $nombreDeClaseAquePertenece = "";

                            foreach ($elementos as $e) {
                                if($e->type == 'standard.Rectangle'){
                                    if($e->id == $targetID){
                                        $nombreDeClaseAquePertenece = $e->attrs->headerText->text; 
                                        break;
                                    }
                                }
                            }




                            $metodos[$nombreDeClaseAquePertenece][] = "public void {$nombreFuncion}({$var}){\n}";

                            /* dd($metodos); */
                            
                        } 
                    

                   } 


                   
                }
                
            }
            
        } 


        

        /* dd($metodos); */

        $nombresClases = array_values($clases);

        /* dd($nombresClases); */




        $formatoClase = [];

        foreach ($nombresClases as $clase) {

            $mayus = ucfirst($clase);
            if(isset($metodos[$clase])){
                $variable = implode("\n", $metodos[$clase]);
                $formatoClase[$mayus]  = "public class {$mayus} { \n $variable\n}" ;
            }else{
                $formatoClase[$mayus]  = "public class {$mayus} {\n}" ;
            }
            

        }

        /* dd($formatoClase); */


        //
            $directorioDestino = "C:/Users/Pepe/Downloads/aquiC#/";

            // Verifica si el directorio de destino existe y, si no, créalo
            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0777, true);
            }

            foreach ($formatoClase as $key => $class) {
                // Genera un nombre de archivo único para cada class
                $nombreArchivo = $directorioDestino.$key.".cs";

                // Abre el archivo para escritura
                $archivo = fopen($nombreArchivo, 'w');

                if ($archivo) {
                    // Escribe el class en el archivo
                    fwrite($archivo, $class);
                    
                    // Cierra el archivo
                    fclose($archivo);
                    
                    
                } 
            }

            return redirect()->back()->with('message', 'se han generado exitosamente los archivos en C#');
        //

    }


    public function generarDartCode(Diagrama $diagrama){
        $nombre = $diagrama->nombre;
        $contenido = json_decode($diagrama->contenido); //arroja en formato json la info de las componentes graficas del diagrama
        
        /* dd($contenido); */

        $elementos = $contenido->cells; //especificamos la var elementos como la var que accedera a la info de los componentes
        /* dd($elementos); */
          
        $clases = []; //declaramos un array para guardar el nombre de todas las clases que se generen del diagrama
        $metodos = []; //declaramos un array para guardar el nombre de todas los metodos que se generen del diagrama

        
        foreach ($elementos as $element) { // vamos a recorrer el array de objetos que estan en formato json (componentes del diagrama)
            if($element->type == 'standard.EmbeddedImage' or $element->type == 'erd.Entity'){ // preguntamos si el elemento es de tipo actor, boundary, control o entity 

                if($element->type == 'standard.EmbeddedImage'){

                    $clases[$element->id] = $element->attrs->headerText->text; //vamos a guardar en el array de clases, el nombre de la clase en la posicion determinada por el id del objeto

                }else{
                    $clases[$element->id] = $element->attrs->text->text;
                }
                /* dd($clases[$element->id]); */

            }else if($element->type == 'app.Link'){  //preguntamos si el elemento es del tipo flecha o conector
               
                if(isset($element->id)){  // preguntamos si la flecha o connector tiene un atributo id y este no es nulo
                  
                   if(count($element->labels) > 0){ // preguntamos si la felcha o connector tiene una etiqueta(esa etiqueta representa una funcion/metodo)

                        $cadenaMetodo = $element->labels[0]->attrs->text->text; //asignamos a una variable la cadena que representa a esa funcion/metodo

                        // Patrón de expresión regular para encontrar el nombre de la función y los parámetros
                        $patron = '/^(\w+)\(([^)]*)\)$/'; // validamos que la cadena tenga la forma: nombrefuncion(tipo parametro, tipo parametro)

                        // Realizar la coincidencia con la expresión regular
                        if (preg_match($patron, $cadenaMetodo, $coincidencias)) {  // preguntamos si la cadena que representa a la funcion/metodo cumple con el patron descrito 
                            // El nombre de la función se encuentra en $coincidencias[1]
                            $nombreFuncion = $coincidencias[1];

                            // Los parámetros se encuentran en $coincidencias[2]
                            $parametros = explode(',', $coincidencias[2]); // array de subcadenas llamado $parametros que guarda los parametros de la funcion/metodo

                            $parametros = array_map('trim', $parametros); // eliminamos los espacios en blanco al principio y al final de cada parametro de la funcion/metodo y  lo volvemos a cqrgar en el array $parametros

                            $parametrosFormateados = []; //declaramos una array vacio para guardar los parametros 'tipo parametro' con el formato de java

                            foreach ($parametros as $parametro) { // recorremos el array $parametros 
                                
                                $arrayParametro = explode(' ', $parametro); // creamos un array de subcadenas donde guardamos el tipo del parametro y el parametro

                                if( strtolower($arrayParametro[0]) == 'int'){ // convierte el primer elemento del arreglo en minusculas y compara

                                    $parametrosFormateados[] = "int {$arrayParametro[1]}"; // inserta en el array con la forma "int parametro"
                                     
                                }else if(strtolower($arrayParametro[0]) == 'String'){ // convierte el primer elemento del arreglo en minusculas y compara

                                    $parametrosFormateados[] = "string {$arrayParametro[1]}"; // inserta en el array con la forma "String parametro"

                                }else if(strtolower($arrayParametro[0]) == 'bool'){ // convierte el primer elemento del arreglo en minusculas y compara

                                    $parametrosFormateados[] = "bool {$arrayParametro[1]}"; // inserta en el array con la forma "boolean parametro"

                                }
                                
                            }

                            $var = implode(',', $parametrosFormateados); // aqui unimos en una sola cadena separada por comas los elementos del array $parametrosFormateados

                            $targetID = $element->target->id; //guarda el id de la flecha que coincide con el id de la casilla de activacion que contiene el nombre de la clase a la que pertenece el metodo que viaja en la flecha

                            $nombreDeClaseAquePertenece = "";

                            foreach ($elementos as $e) {
                                if($e->type == 'standard.Rectangle'){
                                    if($e->id == $targetID){
                                        $nombreDeClaseAquePertenece = $e->attrs->headerText->text; 
                                        break;
                                    }
                                }
                            }




                            $metodos[$nombreDeClaseAquePertenece][] = "void {$nombreFuncion}({$var}){\n}";

                            /* dd($metodos); */
                            
                        } 
                    

                   } 


                   
                }
                
            }
            
        } 


        

        /* dd($metodos); */

        $nombresClases = array_values($clases);

        /* dd($nombresClases); */




        $formatoClase = [];

        foreach ($nombresClases as $clase) {

            $mayus = ucfirst($clase);
            if(isset($metodos[$clase])){
                $variable = implode("\n", $metodos[$clase]);
                $formatoClase[$mayus]  = "class {$mayus} { \n $variable\n}" ;
            }else{
                $formatoClase[$mayus]  = "class {$mayus} {\n}" ;
            }
            

        }

        /* dd($formatoClase); */


        //
            $directorioDestino = "C:/Users/Pepe/Downloads/aquiDart/";

            // Verifica si el directorio de destino existe y, si no, créalo
            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0777, true);
            }

            foreach ($formatoClase as $key => $class) {
                // Genera un nombre de archivo único para cada class
                $nombreArchivo = $directorioDestino.$key.".dart";

                // Abre el archivo para escritura
                $archivo = fopen($nombreArchivo, 'w');

                if ($archivo) {
                    // Escribe el class en el archivo
                    fwrite($archivo, $class);
                    
                    // Cierra el archivo
                    fclose($archivo);
                    
                    
                } 
            }

            return redirect()->back()->with('message', 'se han generado exitosamente los archivos en DART');
        //

    }


    
}
