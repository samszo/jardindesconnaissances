///////////////////////////////////////
// COPYRIGHT ERNO AAPA - FINFLEX.FI
//
// Free for personal usage only
// To use in comercial contact me first
//
///////////////////////////////////////
package com.finflex.piemenu{
	
	import com.degrafa.Surface;
	import com.degrafa.core.IGraphicsFill;
	import com.degrafa.core.IGraphicsStroke;
	import com.degrafa.paint.GradientFill;
	import com.degrafa.paint.GradientStop;
	import com.degrafa.paint.SolidStroke;
	
	import flash.events.MouseEvent;
	import flash.events.TimerEvent;
	import flash.utils.Timer;
	
	import mx.collections.ArrayCollection;
	import mx.events.FlexEvent;
	import mx.styles.StyleManager;
	
	import com.finflex.piemenu.PieMenuEvent;
	import com.finflex.piemenu.MenuItem;
		
	[Event(name="menuClosed",type="erno.menus.PieMenuEvent")]
	[Event(name="itemClosed",type="erno.menus.PieMenuEvent")]
	
	public class PieMenu extends Surface{
		
		public function PieMenu(width:Number=0,height:Number=0,Size:Number=270,startAngle:Number=0,gap:Number=0,thickness:Number=0.5):void{
			
			//default stroke
			var defaultStroke:SolidStroke = new SolidStroke();
			defaultStroke.color = 0x000000;
			defaultStroke.weight = 2;
			defaultStroke.alpha = 0.5;
			
			this.stroke = defaultStroke;
		
			//make default fill
            var defaultFill:GradientFill = new GradientFill();
            defaultFill.angle = 90;
            var fillGradStop1:GradientStop = new GradientStop(StyleManager.getColorName("#a0c1ed"), 1,0.5);
            var fillGradStop2:GradientStop = new GradientStop(StyleManager.getColorName("#cbe1fe"), 1,1);
            defaultFill.gradientStopsCollection.addItem(fillGradStop1);
            defaultFill.gradientStopsCollection.addItem(fillGradStop2);
			
			this.fill = defaultFill;
			
			//make default mouse over fill
            var defaultOverFill:GradientFill = new GradientFill();
            defaultOverFill.angle = 90;
            var overFillGradStop1:GradientStop = new GradientStop(StyleManager.getColorName("#cbe1fe"), 1,0.3);
            var overFillGradStop2:GradientStop = new GradientStop(StyleManager.getColorName("#ffffff"), 1);
            defaultOverFill.gradientStopsCollection.addItem(overFillGradStop1);
            defaultOverFill.gradientStopsCollection.addItem(overFillGradStop2);
			
			this.mouseOverFill = defaultOverFill;
			
			//make default mouse down fill
            var defaultDownFill:GradientFill = new GradientFill();
            defaultDownFill.angle = 90;
            var downFillGradStop1:GradientStop = new GradientStop(StyleManager.getColorName("#6184a9"), 1,0.3);
            var downFillGradStop2:GradientStop = new GradientStop(StyleManager.getColorName("#a0c1ed"), 1);
            defaultDownFill.gradientStopsCollection.addItem(downFillGradStop1);
            defaultDownFill.gradientStopsCollection.addItem(downFillGradStop2);
			
			this.mouseDownFill = defaultDownFill;
			
			this.width = width;
			this.height = height;
			this.size = Size;
			this.startAngle = startAngle;
			this.gap = gap;
			this.thickness = thickness;
			this.name = "piemenuRoot";
			this.addEventListener(MouseEvent.ROLL_OVER, mouseRollOverHandler);
			this.addEventListener(FlexEvent.CREATION_COMPLETE, updateParents);
			
		}
		
		public function show():void{
			this.refreshSubItems();
		}
		
		
		/**
		 * When creation is compelete update:
		 *  - MenuItem.parentItem 
		 *  - MenuItem.parentMenu
		 *    - what updates MenuItem.target -value
		 * 
		 * This goes trough all items in menu
		 * */
		private function updateParents(event:FlexEvent):void{
			for(var i:int=0;i<items.length;i++){
				items[i].setParents(this, this);
			}
		}
				
		
		/**
		 * to set/get default stroke
		 * */
		private var _stroke:IGraphicsStroke;
		public function set stroke(value:IGraphicsStroke):void{
			if(_stroke != value){
				if(value){
					_stroke = value;
				}
			}
		}
		public function get stroke():IGraphicsStroke{
			return _stroke;
		}
		
		
		/**
		 * to set/get default mouse over stroke
		 * */
		private var _mouseOverStroke:IGraphicsStroke;
		public function set mouseOverStroke(value:IGraphicsStroke):void{
			if(_mouseOverStroke != value){
				if(value){
					_mouseOverStroke = value;
				}
			}
		}
		public function get mouseOverStroke():IGraphicsStroke{
			return _mouseOverStroke;
		}
		
		
		/**
		 * to set/get default mouse down stroke
		 * */
		private var _mouseDownStroke:IGraphicsStroke;
		public function set mouseDownStroke(value:IGraphicsStroke):void{
			if(_mouseDownStroke != value){
				if(value){
					_mouseDownStroke = value;
				}
			}
		}
		public function get mouseDownStroke():IGraphicsStroke{
			return _mouseDownStroke;
		}
		
		
		/**
		 * to set/get default fill
		 * */
		private var _fill:IGraphicsFill;
		public function set fill(value:IGraphicsFill):void{
			if(_fill != value){
				if(value){
					_fill = value;
				}
			}
		}
		public function get fill():IGraphicsFill{
			return _fill;
		}
		
		
		/**
		 * to set/get default mouse over fill
		 * */
		private var _mouseOverFill:IGraphicsFill;
		public function set mouseOverFill(value:IGraphicsFill):void{
			if(_mouseOverFill != value){
				if(value){
					_mouseOverFill = value;
				}
			}
		}
		public function get mouseOverFill():IGraphicsFill{
			return _mouseOverFill;
		}
		
		
		/**
		 * to set/get default mouse down fill
		 * */
		private var _mouseDownFill:IGraphicsFill;
		public function set mouseDownFill(value:IGraphicsFill):void{
			if(_mouseDownFill != value){
				if(value){
					_mouseDownFill = value;
				}
			}
		}
		public function get mouseDownFill():IGraphicsFill{
			return _mouseDownFill;
		}
		
		
		/**
		 * to set how big is size
		 * value 1-360
		 * */
		private var _size:Number=180;
		public function set size(value:Number):void{
			if(_size != value){
				_size = value;
				refreshSubItems();
			}
		}
		public function get size():Number{
			return _size;
		}
		
		
		/**
		 * to set/get start angle where menu starts
		 * */
		private var _startAngle:Number=0;
		public function set startAngle(value:Number):void{
			if(_startAngle != value){
				_startAngle = value;
				refreshSubItems();
			}
		}
		public function get startAngle():Number{
			return _startAngle;
				refreshSubItems();
		}
		
		
		/**
		 * to set/get thickness
		 * how many % of full width is middle hole
		 * if value is 0 its thick as possible
		 * if value is 0.99 item thickness is only 0.01% of full height
		 * value 1 cant be. 
		 * */
		private var _thickness:Number=0;
		public function set thickness(value:Number):void{
			if(_thickness != value){
				_thickness = value;
				refreshSubItems();
			}
		}
		public function get thickness():Number{
			return _thickness;
		}
		
		
		/**
		 * to set/get gap between items
		 * */
		private var _gap:Number=0;
		public function set gap(value:Number):void{
			if(_gap != value){
				_gap = value;
				refreshSubItems();
			}
		}
		public function get gap():Number{
			return _gap;
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
		 * temporary testing
		 * */
		private var _iconRotate:Number = 0;
		public function get iconRotate():Number{
			return _iconRotate;
		}
		public function set iconRotate(value:Number):void{
			_iconRotate = value;
			refreshSubItems();
		}
		
		
		/**
		 * to get open sub item
		 * */		
		public function get getOpenSubItem():MenuItem{
			return _openSubItem;
		}
		
		
		/**
		 * Mouse roll over handler
		 * when mouse roll over button add listener to mouse roll out
		 * so mouse have to roll over item at least once to close menu
		 * - set lockMenu = true if you want to keep menu visible
		 * - use closeTime variable to set close delay
		 * */
		public var closeTime:Number = 1000;
		private var _rollOutTimer:Timer = new Timer(closeTime, 1);
		public var lockMenu:Boolean = false;
		private function mouseRollOverHandler(event:MouseEvent):void{
			if(lockMenu == false){
				if(_rollOutTimer.running){
					_rollOutTimer.stop();
				}
				this.addEventListener(MouseEvent.ROLL_OUT,mouseRollOutHandler);
			}
		}
		
		/**
		 * mouse roll out handler
		 * when mouse roll out start roll out timer so we get little delay to closing
		 * */
		private function mouseRollOutHandler(event:MouseEvent):void{
			this.removeEventListener(MouseEvent.ROLL_OUT,mouseRollOutHandler);
			
		 	if(!_rollOutTimer.running){
		 		_rollOutTimer = new Timer(closeTime,1);
		 		_rollOutTimer.addEventListener(TimerEvent.TIMER_COMPLETE,rollOutTimerHandler);
		 		_rollOutTimer.start();
		 	}
		 	
		}
		
		/**
		 * when close timer is over close menu
		 * */
		private function rollOutTimerHandler(event:TimerEvent):void{	 	
		 	hide();
		}
		
		
		/**
		 * to add new menuItem
		 * */
		public function addItem(item:MenuItem):void{
			
			//add it to parentItem's subitems-arraycollection
			this.addSubItem(item);
		}
		
		/**
		 * to add new item to subItems-arraycollection
		 * */
		public function addSubItem(item:MenuItem):void{
			_items.addItem(item);
		}
		
		
		/**
		 * Refresh (redraw) this subitems
		 * */
		public function refreshSubItems():void{
			
			for(var i:int=0;i<_items.length;i++){
				var item:MenuItem = _items.getItemAt(i) as MenuItem;
				item.refreshItem();
			}
			
		}
		
		/**
		 * to open subItem
		 * if other subItem is open we have to close it first
		 * */
		 private var _openSubItem:MenuItem;
		 private var _newOpenSubItem:MenuItem;
		 public function openSubItem(item:MenuItem):void{
		 
		 	if(_openSubItem != item){
			 	//add item to memory
			 	_newOpenSubItem = item;
			 	//if there is item open close it
			 	closeSubItem();
		 	}
		 	
		 }
		 
		 /**
		 *to close subItem
		 * if there is openSubItem ->close it
		 * */
		 public function closeSubItem():void{
		 	if(_openSubItem){
		 		_openSubItem.addEventListener(PieMenuEvent.ITEM_CLOSED,openSubItemClosedHandler);
		 		_openSubItem.closeSubItem();
		 	}else{
		 		openNewSubItem();
		 	}
		 }
		 private function openSubItemClosedHandler(event:PieMenuEvent):void{
		 	_openSubItem.removeEventListener(PieMenuEvent.ITEM_CLOSED,openSubItemClosedHandler);
		 	openNewSubItem();
		 }
		 
		 /**
		 * when open sub item is closed this opens new subItem
		 * */
		 private function openNewSubItem():void{
		 	
		 	_openSubItem = _newOpenSubItem as MenuItem;
		 	_openSubItem.refreshSubItems();
		 	
		 }
		 
		 
		/**
		 * function to hide pie
		 * loops trough all sub items and close them
		 * ! DONT REMOVE MENU FROM STAGE
		 * */
		public function hide():void{
			
			for(var i:int=0;i<_items.length;i++){
		 		var item:MenuItem = _items.getItemAt(i) as MenuItem;
		 		item.addEventListener(PieMenuEvent.ITEM_CLOSED, subItemsClosedHandler);
		 		item.closeSubItem();
		 	}
		 	_openSubItem = null;
		 	
		}
		
		private function subItemsClosedHandler(event:PieMenuEvent):void{
			var item:MenuItem = event.target as MenuItem;
			item.removeEventListener(PieMenuEvent.ITEM_CLOSED, subItemsClosedHandler);
			item.closeSelf();
		}
				
				
	}
}