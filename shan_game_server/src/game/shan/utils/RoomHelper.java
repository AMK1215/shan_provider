package game.shan.utils;

import com.smartfoxserver.v2.entities.Room;
import com.smartfoxserver.v2.extensions.BaseClientRequestHandler;
import com.smartfoxserver.v2.extensions.BaseServerEventHandler;
import com.smartfoxserver.v2.extensions.SFSExtension;

import game.shan.ShanExtension;
import game.shan.game.SKMGame;

// Helper methods to easily get current room or zone and precache the link to ExtensionHelper
public class RoomHelper {

	public static Room getCurrentRoom(BaseClientRequestHandler handler) {
		return handler.getParentExtension().getParentRoom();
	}
	
	public static Room getCurrentRoom(BaseServerEventHandler handler) {
		return handler.getParentExtension().getParentRoom();
	}


	public static Room getCurrentRoom(SFSExtension extension) {
		return extension.getParentRoom();
	}

	public static SKMGame getGame(BaseClientRequestHandler handler) {
		ShanExtension ext = (ShanExtension) handler.getParentExtension();
		return ext.getGame();
	}

	public static SKMGame getGame(BaseServerEventHandler handler) {
		ShanExtension ext = (ShanExtension) handler.getParentExtension();
		return ext.getGame();
	}


}
