import java.sql.*;
import java.io.File;
import java.io.IOException;
import java.util.ArrayList;
import org.ini4j.Wini;

public class GestionBDD {
	
	private Wini ini;
	private String host, dbname, user, password;
	
	public GestionBDD() throws IOException
	{
		Wini ini = new Wini(new File("conf.ini"));
        host = ini.get("database", "host");
        dbname = ini.get("database", "dbname");
        user = ini.get("database", "user");
        password = ini.get("database", "password");
	}
	
	@SuppressWarnings("finally")
	public ArrayList<String> getTests(int projectId, String testName)
	{
		ArrayList<String> testsAvailable = new ArrayList<String>();
		
		Connection con = null;
		Statement st = null;
		ResultSet rs = null;
		
		try
		{
			Class.forName("com.mysql.jdbc.Driver").newInstance();
			con = DriverManager.getConnection("jdbc:mysql://" + host + "/" + dbname, user, password);
			st = con.createStatement();
			
			//System.out.println("SELECT subtest.name FROM subtest WHERE subtest.test_name = '" + testName + "' AND subtest.project_id=" + projectId);
			rs = st.executeQuery("SELECT subtest.name FROM subtest WHERE subtest.test_name = '" + testName + "' AND subtest.project_id=" + projectId);
			
			while (rs.next())
			{
				testsAvailable.add(rs.getString("name"));
			}
		}
		catch(Exception e)
		{
			System.err.println("Exception : " + e.getMessage());
		}
		
		finally
		{
			try
			{
				if(rs != null)
					rs.close();
				if(st != null)
					st.close();
				if(con != null)
					con.close();
			}
			catch(Exception e){}
			
			return testsAvailable;
		}
	}
	
	public void addResult(int projectId, String testName, String subtestName, String username, int status, String errors)
	{
		Connection con = null;
		Statement st = null;
		ResultSet rs = null;
		
		try
		{
			Class.forName("com.mysql.jdbc.Driver").newInstance();
			con = DriverManager.getConnection("jdbc:mysql://" + host + "/" + dbname, user, password);
			st = con.createStatement();
			//System.out.println("INSERT INTO users_test (project_id, test_name, subtest_name, username, status, errors) VALUES ('" + projectId + "', '" + testName + "', '" + subtestName + "', '" + username + "', " + Integer.toString(status) + ", '" + errors + "')");
			st.executeUpdate("INSERT INTO users_test (project_id, test_name, subtest_name, username, status, errors) VALUES ('" + projectId + "', '" + testName + "', '" + subtestName + "', '" + username + "', " + Integer.toString(status) + ", '" + errors + "')");
		}
		catch(Exception e)
		{
			System.err.println("Exception : " + e.getMessage());
		}
		
		finally
		{
			try
			{
				if(rs != null)
					rs.close();
				if(st != null)
					st.close();
				if(con != null)
					con.close();
			}
			catch(Exception e){}
		}
	}

}