package game.shan.serializeclasses;

public class LoginReq {
	public String user_name;
	public String password;
	
	public LoginReq(String name, String psw) {
		user_name = name;
		password = psw;
	}
}
