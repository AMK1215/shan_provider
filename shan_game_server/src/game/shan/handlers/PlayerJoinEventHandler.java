package game.shan.handlers;

import com.smartfoxserver.v2.core.ISFSEvent;
import com.smartfoxserver.v2.core.SFSEventParam;
import com.smartfoxserver.v2.core.SFSEventType;
import com.smartfoxserver.v2.entities.User;
import com.smartfoxserver.v2.exceptions.SFSException;
import com.smartfoxserver.v2.extensions.BaseServerEventHandler;

import game.shan.game.SKMGame;
import game.shan.utils.RoomHelper;

public class PlayerJoinEventHandler extends BaseServerEventHandler {

	@Override
	public void handleServerEvent(ISFSEvent event) throws SFSException {
		
		if (event.getType() == SFSEventType.USER_JOIN_ROOM)
		{
			User user = (User) event.getParameter(SFSEventParam.USER);

			SKMGame game = RoomHelper.getGame(this);
			
			if(user.getLastJoinedRoom().getUserList().size() == 1) {
				trace("sending owner and creating bots");
				game.processAddPlayer(user);
				//game.sendOwnerWithDelay(user, true);
				//game.processCreateBots(user.getLastJoinedRoom());
				game.addBots(user.getLastJoinedRoom());
				game.sendStartDelay();
			}
			else {
				game.processAddPlayer(user);
			}
		}
	}

}
