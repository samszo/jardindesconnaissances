///////////////////////////////////////
// COPYRIGHT ERNO AAPA - FINFLEX.FI
//
// Free for personal usage only
// To use in comercial contact me first
//
///////////////////////////////////////
package com.finflex.piemenu{
	
	import caurina.transitions.Tweener;
	
	import com.degrafa.GeometryGroup;
	import com.degrafa.GraphicImage;
	import com.degrafa.GraphicText;
	import com.degrafa.IGraphic;
	import com.degrafa.core.IGraphicsFill;
	import com.degrafa.core.IGraphicsStroke;
	import com.degrafa.paint.SolidFill;
	import com.degrafa.paint.SolidStroke;
	
	import flash.display.DisplayObject;
	import flash.events.MouseEvent;
	
	import mx.collections.ArrayCollection;
	import mx.events.PropertyChangeEvent;
	
	import com.finflex.piemenu.PieMenuEvent;
	import com.finflex.piemenu.MenuItem;
	
	[DefaultProperty("items")]
	
	[Event(name="itemClosed",type="erno.menus.PieMenuEvent")]
	
	public class MenuItem extends GeometryGroup implements IGraphic{
			
		private var _arcGraphic:ArcGraphic;
		
		public function MenuItem(){
						
			_arcGraphic = new ArcGraphic(0,0,0,0,0,0,0,"pie");
			
			_arcGraphic.target = this;
			
			_arcGraphic.addEventListener(PropertyChangeEvent.PROPERTY_CHANGE,refreshArc);
			this.addEventListener(MouseEvent.MOUSE_OVER,mouseOverHandler);
			this.addEventListener(MouseEvent.MOUSE_DOWN,mouseDownHandler);
			this.addEventListener(MouseEvent.MOUSE_UP,mouseUpHandler);
							
			this.geometryCollection.addItem(_arcGraphic);
						
		}
		
		
		/**
		 * to get/set this items parent item
		 * */
		private var _parentItem:Object;
		public function set parentItem(item:Object):void{
			_parentItem = item;
		}
		
		public function get parentItem():Object{
			return _parentItem;
		}
		
		private var _parentMenu:PieMenu;
		public function set parentMenu(item:PieMenu):void{
			if(_parentMenu != item){
				_parentMenu = item;
				this.target = _parentMenu;
				this.refresh();
			}
		}
		
		public function get parentMenu():PieMenu{
			return _parentMenu;
		}
		
		/**
		 * after menu is completed this function updates parent -values
		 * goes trough whole menu
		 * */
		public function setParents(parentItem:Object, parentMenu:PieMenu):void{
			this.parentItem = parentItem;
			this.parentMenu = parentMenu;
			for(var i:int=0;i<items.length;i++){
				items[i].setParents(this, parentMenu);
			}
		}
		
		
		/**
		 * to set/get start angle
		 * update it straight to _arcGraphic
		 * */
		private var _startAngle:Number;
		public function set startAngle(value:Number):void{
			if(_startAngle != value){
				_startAngle = value;
			}
		}
		public function get startAngle():Number{
			if(_startAngle){
				return _startAngle;
			}else{
				return _parentItem.startAngle;
			}
		}
		
		/**
		 * to set/get size
		 * */
		private var _size:Number;
		public function set size(value:Number):void{
			if(_size != value){
				_size = value;
			}
		}
		public function get size():Number{
			return _size;
		}
		
		/**
		 * to set/get thickness
		 * if dont have own thickness return parent item thickness
		 * */
		private var _thickness:Number;
		public function set thickness(value:Number):void{
			if(_thickness != value){
				_thickness = value;
			}
		}
		public function get thickness():Number{
			if(_thickness >= 0){
				return _thickness;
			}else{
				return parentItem.thickness;
			}
		}
		
		/**
		 * to set/get gap
		 * if dont have own gap value return parent item gap
		 * */
		private var _gap:Number;
		public function set gap(value:Number):void{
			if(_gap != value){
				_gap = value;
			}
		}
		public function get gap():Number{
			if(_gap >= 0){
				return _gap;
			}else{
				return parentItem.gap;
			}
		}
		
		/**
		 * override function to get/Set stroke
		 * this is overrided because if we dont have stroke we have to get it 
		 * from parentItem
		 * */
		private var _stroke:IGraphicsStroke;
		public override function get stroke():IGraphicsStroke{
			if(_stroke){
				return _stroke;
			}else{
				if(!parentItem){
					return new SolidStroke();
				}else{
					return parentItem.stroke;
				}
			}
		}
		public override function set stroke(value:IGraphicsStroke):void{
			_stroke = value;
			_arcGraphic.stroke = value;
		}
		
		
		/**
		 * override function to get/Set mouse over stroke
		 * get it from parentItem if dont have own
		 * */
		private var _mouseOverStroke:IGraphicsStroke;
		public function get mouseOverStroke():IGraphicsStroke{
			if(_mouseOverStroke){
				return _mouseOverStroke;
			}else{
				return parentItem.mouseOverStroke;
			}
		}
		public function set mouseOverStroke(value:IGraphicsStroke):void{
			_mouseOverStroke = value;
		}
		
		
		/**
		 * override function to get/Set mouse down stroke
		 * get it from parentItem if dont have own
		 * */
		private var _mouseDownStroke:IGraphicsStroke;
		public function get mouseDownStroke():IGraphicsStroke{
			if(_mouseDownStroke){
				return _mouseDownStroke;
			}else{
				return parentItem.mouseDownStroke;
			}
		}
		public function set mouseDownStroke(value:IGraphicsStroke):void{
			_mouseDownStroke = value;
		}
		
		
		/**
		 * override function to get/Set fill
		 * this is overrided because if we dont have fill we have to get it 
		 * from parentItem
		 * */
		private var _fill:IGraphicsFill;
		public override function get fill():IGraphicsFill{
			if(_fill){
				return _fill;
			}else{
				if(!parentItem){
					return new SolidFill();
				}else{
					return parentItem.fill;
				}
			}
		}
		
		public override function set fill(value:IGraphicsFill):void{
			_fill = value;
			_arcGraphic.fill = value;
		}
		
		
		/**
		 * override function to get/Set mouse over fill
		 * get it from parentItem if dont have own
		 * */
		private var _mouseOverFill:IGraphicsFill;
		public function get mouseOverFill():IGraphicsFill{
			if(_mouseOverFill){
				return _mouseOverFill;
			}else{
				return parentItem.mouseOverFill;
			}
		}
		public function set mouseOverFill(value:IGraphicsFill):void{
			_mouseOverFill = value;
		}
		
		
		/**
		 * override function to get/Set mouse down fill
		 * get it from parentItem if dont have own
		 * */
		private var _mouseDownFill:IGraphicsFill;
		public function get mouseDownFill():IGraphicsFill{
			if(_mouseDownFill){
				return _mouseDownFill;
			}else{
				if(this.parent is MenuItem){
					var testPar:MenuItem = this.parent as MenuItem;
					return testPar.mouseDownFill;
				}else{
					var testPar2:PieMenu = this.parent as PieMenu;
					return testPar2.mouseDownFill;
				}
			}
		}
		public function set mouseDownFill(value:IGraphicsFill):void{
			_mouseDownFill = value;
		}
		
		private var _items:ArrayCollection  = new ArrayCollection();
		[Inspectable(category="General", arrayType="com.ernoaapa.piemenu.MenuItem")]
		[ArrayElementType("com.finflex.piemenu.MenuItem")]
		public function get items():Array{
			return _items.source;
		}
		public function set items(value:Array):void{
			for(var i:int=0;i<value.length;i++){
				
				if(value[i] is MenuItem){
					this.addItem(value[i]);
				}
			}
		}
		
		/**
		 * to add new menuItem
		 * */
		public function addItem(item:MenuItem):void{
				
			//add it to parentItem's items-arraycollection
			this.addSubItem(item);
			
		}
		
		/**
		 * to get open subItem
		 * */		
		public function get getOpenSubItem():MenuItem{
			return _openSubItem;
		}
		
		
		/**
		 * to get width/height
		 * this group dont have width/height but _arcGraphic have
		 * */
		public override function get width():Number{
			return _arcGraphic.width;
		}
		public override function get height():Number{
			return _arcGraphic.height;
		}
		
		
		/**
		 * icon
		 * not ready yet
		 * */
		private var _icon:Object;
		public function set icon(value:IGraphic):void{
			
			if(value){
				_icon = value;
				
				if(value is GraphicImage){
					_icon.scaleX = 0;
					_icon.scaleY = 0;
					_icon.mouseEnabled = false;
				
				}else if(value is GraphicText){
					_icon.mouseEnabled = false;
										
				}
			}
		}
		public function get icon():IGraphic{
			return _icon as IGraphic;
		}
		
		/**
		 * to refresh this geometryGroup
		 * all changes to _arcGraphic apply
		 * */
		public function refresh():void{
			this.draw(null,null);
		}
		
		/**
		 * for arcGraphic change event listener
		 * */
		private function refreshArc(event:PropertyChangeEvent):void{
			refresh();
			if(parentItem){
				if(parentItem.getOpenSubItem == this){
					refreshSubItems();
				}
			}
		}
		
		private function mouseDownHandler(event:MouseEvent):void{
			_arcGraphic.fill = this.mouseDownFill;
			refresh();
		}
		
		private function mouseUpHandler(event:MouseEvent):void{
			_arcGraphic.fill = this.mouseOverFill;
			refresh();
		}
		
		private function mouseOverHandler(event:MouseEvent):void{
			//change fill if mouse over
			if(_tweening == false){
				_arcGraphic.fill = this.mouseOverFill;
				refresh();
				this.addEventListener(MouseEvent.MOUSE_OUT,mouseOutHandler);
				_parentItem.openSubItem(this);
			}
		}
		
		private function mouseOutHandler(event:MouseEvent):void{
			//change fill back
			this.removeEventListener(MouseEvent.MOUSE_OUT,mouseOutHandler);
			_arcGraphic.fill = this.fill;
			refresh();
		}
		
		/**
		 * to add new item to items-arraycollection
		 * */
		public function addSubItem(item:MenuItem):void{
			_items.addItem(item);
			
		}
		
		public function refreshSubItems():void{
			for(var i:int=0;i<_items.length;i++){
				var item:MenuItem = _items.getItemAt(i) as MenuItem;
				item.refreshItem();
			}
			
		}
		
		
		/**
		 * to recalculate arcComponent values
		 * */	
		private var _tweening:Boolean = false;
		public function refreshItem():void{
			
			var newSize:Number;
			var newStartAngle:Number;
			var thisParentItem:Object;
			var newWidth:Number;
			var newHeight:Number;
			var newThickness:Number;
			var startAngleFix:Number = 0;
			
			//if parent is menuItem not root
			// fix this later
			if(this.parentItem is MenuItem){
				
				thisParentItem = this.parentItem as MenuItem;

				newWidth = thisParentItem.width+thisParentItem.width*(this.thickness);
				newHeight = thisParentItem.height+thisParentItem.height*(this.thickness);
				
				newThickness = 1-(_parentItem.width/newWidth);
				
				newSize = (thisParentItem.size-this.gap*(thisParentItem.items.length-1))/thisParentItem.items.length;
				
				newStartAngle = thisParentItem.startAngle+((newSize+this.gap)*thisParentItem.items.indexOf(this));
					
			
			//if parentItem is root (pieMenu)
			}else if(this.parentItem is PieMenu){
				
				thisParentItem = this.parentItem as PieMenu;
				
				newWidth = thisParentItem.width;
				newHeight = thisParentItem.height;
				
				newThickness = thisParentItem.thickness;

				newSize = (thisParentItem.size-this.gap*thisParentItem.items.length)/thisParentItem.items.length;
				
				newStartAngle = thisParentItem.size+this.gap-(this.gap/2)+((newSize+this.gap)*thisParentItem.items.indexOf(this));
				newStartAngle = newStartAngle+thisParentItem.startAngle;
			}	
			
			_size = newSize;
			_startAngle = newStartAngle;
			
			
			var newX:Number = -newWidth/2;
			var newY:Number = -newHeight/2;

			_tweening = true;
			Tweener.addTween(_arcGraphic,{arc:newSize,
											startAngle:newStartAngle,
											innerRadius:newThickness,
											width:newWidth,
											height:newHeight,
											x:newX,
											y:newY,
											onComplete:showIcon,
											time:0.5,transition:"easeInOutCirc"});
			
			
				
			//temporary testing
			if(_icon){
				//update icon position
				var _testX:Number = (newWidth/2)*Math.cos((newStartAngle+newSize/2)*Math.PI/180);
				var _testY:Number = (newWidth/2)*Math.sin((newStartAngle+newSize/2)*Math.PI/180);
				
				var _testX2:Number = (_testX+newWidth/2)/newWidth;
				var _testY2:Number = (_testY+newWidth/2)/newWidth;
				
				
				_testX = _testX-(_icon.width*_testX2);
				_testY = _testY-(_icon.width*_testY2);
				
				if(_icon is GraphicText){
					_testY += 4;
				}
				
				_icon.x = _testX;
				_icon.y = _testY;
			}
			
			_arcGraphic.fill = this.fill;
			_arcGraphic.stroke = this.stroke;
			
			
			
		}
		
		/**
		 * to show icon
		 * */
		public function showIcon():void{
			_tweening = false;
			this.parentMenu.setChildIndex(_icon as DisplayObject, this.parentMenu.graphicsData.length);
			Tweener.addTween(_icon,{scaleX:1,scaleY:1,time:0.3,transition:"linear"});
		}
		
		
		/**
		 * to open subItem
		 * if other subItem is open we have to close it first
		 * */
		 private var _openSubItem:MenuItem;
		 private var _newOpenSubItem:MenuItem;
		 private var _isOpening:Boolean = false;
		 public function openSubItem(item:MenuItem):void{
		 	
		 	if(_openSubItem != item){
			 	_isOpening = true;
			 	//add item to memory
			 	_newOpenSubItem = item;
			 	//if there is item open close it
			 	closeSubItem();
			 }
		 }
		 
		 /**
		 * to close subItem
		 * if there is openSubItem ->close it
		 * */
		 public function closeSubItem():void{
		 	//if this menuItem have subItem open
		 	if(_openSubItem){
		 		//listen when its closed
		 		_openSubItem.addEventListener(PieMenuEvent.ITEM_CLOSED,openSubItemClosedHandler);
		 		_openSubItem.closeSubItem();
		 	}else{
		 		//if this dont have subItem open just close this subitems
		 		closeThisSubItems();
		 	}
		 }
		 private function openSubItemClosedHandler(event:PieMenuEvent):void{
		 	_openSubItem.removeEventListener(PieMenuEvent.ITEM_CLOSED, openSubItemClosedHandler);
		 	closeThisSubItems();
		 }


		 /**
		 * to close all this MenuItem's subItems
		 * */
		 private function closeThisSubItems():void{
		 	
		 	
		 	if(_isOpening == true){
		 		
				_openSubItem = _newOpenSubItem;
				_openSubItem.refreshSubItems();
				_isOpening = false;
				
		 	}else{
		 		
				for(var i:int=0;i<_items.length;i++){
					var item:MenuItem = _items.getItemAt(i) as MenuItem;
					item.closeSelf();
				}
				this.dispatchEvent(new PieMenuEvent(PieMenuEvent.ITEM_CLOSED));
		 	}
		 }
		 
		 /**
		 * to close this item
		 * just tweens all values to 0
		 * */
		 public function closeSelf():void{
		 	Tweener.addTween(icon,{scaleX:0,scaleY:0,time:0.3,transition:"linear"});
			Tweener.addTween(_arcGraphic,{arc:0,startAngle:0,innerRadius:0,delay:0.3,time:0.5,transition:"easeInOutCirc"});
			Tweener.addTween(_arcGraphic,{width:0,height:0,x:0,y:0,delay:0.5,time:0});
			_startAngle = 0;
			_size = 0;
			_openSubItem = null;
		 }
		
	}
	
	
}