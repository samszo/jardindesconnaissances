package jardin
{
	
	import com.finflex.piemenu.MenuItem;
	import com.finflex.piemenu.PieMenu;
	
	import flash.display.DisplayObject;
	import flash.display.Sprite;
	import flash.events.MouseEvent;
	import flash.geom.Point;

	//http://flaim.wordpress.com/2008/05/05/simple-maths-in-as3-part-4-drawing-polygons/

	public class Boussole extends Sprite
	{
		//some vars
		private var poly_id,ratio,top,coords;
		private var _objExplo:DisplayObject;
		private var point1:Point= new Point(0,0);
		private var _objBouge:Boolean=false;
		public var _pm:PieMenu;
		
		public function Boussole(obj:DisplayObject)
		{
			super();
			//makeCadre();
			_objExplo = obj;
			
			drawPoly(30,6,0,0); //hexagon
			
			_pm = new PieMenu;
			var item_pm:MenuItem = new MenuItem();
			var subitem_pm:MenuItem = new MenuItem();
			item_pm.addSubItem(subitem_pm);
			item_pm.addSubItem(subitem_pm);
			_pm.addItem(item_pm);
			item_pm = new MenuItem();
			subitem_pm = new MenuItem();
			item_pm.addSubItem(subitem_pm);
			item_pm.addSubItem(subitem_pm);
			_pm.addItem(item_pm);
						
			addEventListener( MouseEvent.MOUSE_DOWN, onMouseDown );
			addEventListener( MouseEvent.MOUSE_UP, onMouseUp );
			//addEventListener( MouseEvent.MOUSE_MOVE, onMouseMove);
			
		}

		private function onMouseMove( event:MouseEvent ):void
		{
			if(_objBouge){
			   	_objExplo.x = mouseX;
			   	_objExplo.y = mouseY;
			}
		}

		private function onMouseDown( event:MouseEvent ):void
		{
			_objBouge = true;
			//_objExplo.startDrag( false );
		}

		private function onMouseUp( event:MouseEvent ):void
		{
			_objBouge = true;
			//_objExplo.stopDrag();
		}
		

		//the main function  drawPoly(radius,segments,center x, center y);
		public function drawPoly(r:int,seg:int,cx:Number,cy:Number):void
		{
			 poly_id=0; //->0
			 coords=new Array(); //->0
			 ratio=360/seg; //calculated ratio/step
		 	top=cy-r; //top
		 for(var i:int=0;i<=360;i+=ratio) //the main loop- ratio used here
		 {
			  var px:Number=cx+Math.sin(radians(i))*r; // point X
			  var py:Number=top+(r-Math.cos(radians(i))*r); // point Y
			  coords[poly_id]=new Array(px,py); //2nd level array
		  	if(poly_id>=1)
		  	{
		   		poly_draw(coords[poly_id-1][0],coords[poly_id-1][1],coords[poly_id][0],coords[poly_id][1]); //drawing here
		  	}
		   	poly_id++; // increment for the id
		 }
		 	poly_id=0; //id->0
		};
		//degrees2radians
		public function radians(n:Number):Number
		{
		 return(Math.PI/180*n);
		};
		//the drawing function – you can draw anything you want here – in this case it connects the points with red lines
		public function poly_draw(sx:Number,sy:Number,ex:Number,ey:Number):void
		{
		 	graphics.lineStyle(1,0xFF0000,1);
		 	graphics.moveTo(sx,sy);
		 	graphics.lineTo(ex,ey);
		};

	}
}