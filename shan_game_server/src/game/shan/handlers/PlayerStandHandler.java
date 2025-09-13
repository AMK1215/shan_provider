package game.shan.handlers;

import com.smartfoxserver.v2.entities.User;
import com.smartfoxserver.v2.entities.data.ISFSObject;
import com.smartfoxserver.v2.extensions.BaseClientRequestHandler;

import game.shan.utils.RoomHelper;

public class PlayerStandHandler extends BaseClientRequestHandler {

	@Override
	public void handleClientRequest(User user, ISFSObject data) {
		// TODO Auto-generated method stub
		RoomHelper.getGame(this).processClientStand(user);

	}

}
