# Tutorial: Integrate succeedLEARN with Microsoft Entra ID

This tutorial shows you how to integrate succeedLEARN with Microsoft Entra ID (formerly Azure Active Directory). When you integrate succeedLEARN with Microsoft Entra ID, you can:

- Control in Microsoft Entra ID who has access to succeedLEARN
- Enable your users to be automatically signed in to succeedLEARN with their Microsoft Entra accounts
- Automatically provision and deprovision user accounts
- Manage your accounts in one central location - the Azure portal

## Prerequisites

To get started, you need the following items:

- A Microsoft Entra subscription. If you don't have a subscription, you can get a [free account](https://azure.microsoft.com/free/).
- succeedLEARN single sign-on (SSO) enabled subscription.
- Administrator privileges on your succeedLEARN instance.

## Scenario description

In this tutorial, you configure and test Microsoft Entra SSO and automated user provisioning in a test environment.

succeedLEARN supports:

- **SSO**: SP-initiated and IdP-initiated SSO
- **Provisioning**: Automated user provisioning and deprovisioning (SCIM)

## Supported features

| Feature | Supported |
|---------|-----------|
| SP-initiated SSO | ✓ |
| IdP-initiated SSO | ✓ |
| Just-in-time provisioning | ✓ |
| Automated user provisioning | ✓ |
| Automated group provisioning | ✓ |
| Multiple instances per tenant | ✓ |

## Add succeedLEARN from the gallery

To configure the integration of succeedLEARN into Microsoft Entra ID, you need to add succeedLEARN from the gallery to your list of managed SaaS apps.

1. Sign in to the [Microsoft Entra admin center](https://entra.microsoft.com) as at least a [Cloud Application Administrator](../identity/role-based-access-control/permissions-reference.md#cloud-application-administrator).
2. Browse to **Identity** > **Applications** > **Enterprise applications** > **New application**.
3. In the **Add from the gallery** section, type **succeedLEARN** in the search box.
4. Select **succeedLEARN** from results panel and then add the app. Wait a few seconds while the app is added to your tenant.

Alternatively, you can also use the [Azure Cloud Shell](https://shell.azure.com/) PowerShell cmdlet [New-AzureADServicePrincipal](https://docs.microsoft.com/powershell/module/azuread/new-azureadserviceprincipal) to add the application to your tenant.

---

## Part 1: Configure and test Microsoft Entra SSO for succeedLEARN

Configure and test Microsoft Entra SSO with succeedLEARN using a test user called **B.Simon**. For SSO to work, you need to establish a link relationship between a Microsoft Entra user and the related user in succeedLEARN.

To configure and test Microsoft Entra SSO with succeedLEARN, perform the following steps:

1. **[Configure Microsoft Entra SSO](#configure-microsoft-entra-sso)** - to enable your users to use this feature.
   1. **[Create a Microsoft Entra test user](#create-a-microsoft-entra-test-user)** - to test Microsoft Entra single sign-on with B.Simon.
   2. **[Assign the Microsoft Entra test user](#assign-the-microsoft-entra-test-user)** - to enable B.Simon to use Microsoft Entra single sign-on.
2. **[Configure succeedLEARN SSO](#configure-succeedlearn-sso)** - to configure the single sign-on settings on application side.
   1. **[Create succeedLEARN test user](#create-succeedlearn-test-user)** - to have a counterpart of B.Simon in succeedLEARN that is linked to the Microsoft Entra representation of user.
3. **[Test SSO](#test-sso)** - to verify whether the configuration works.

### Configure Microsoft Entra SSO

Follow these steps to enable Microsoft Entra SSO in the Microsoft Entra admin center.

1. In the Microsoft Entra admin center, on the **succeedLEARN** application integration page, find the **Manage** section and select **single sign-on**.
2. On the **Select a single sign-on method** page, select **SAML**.
3. On the **Set up single sign-on with SAML** page, select the pencil icon for **Basic SAML Configuration** to edit the settings.

   ![Edit Basic SAML Configuration](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/media/tutorial_general_edit.png)

4. On the **Basic SAML Configuration** section, if you wish to configure the application in **IdP** initiated mode, perform the following steps:

   a. In the **Identifier** text box, type a URL using the following pattern: `https://<SUBDOMAIN>.succeedlearn.com/auth/saml2/sp/metadata.php`

   b. In the **Reply URL** text box, type a URL using the following pattern: `https://<SUBDOMAIN>.succeedlearn.com/auth/saml2/sp/saml2-acs.php/succeedlearn`

5. Select **Set additional URLs** and perform the following step if you wish to configure the application in **SP** initiated mode:

   In the **Sign-on URL** text box, type a URL using the following pattern: `https://<SUBDOMAIN>.succeedlearn.com/auth/saml2/login.php`

   > [!NOTE]
   > These values are not real. Update these values with the actual Identifier, Reply URL, and Sign-on URL. Contact [succeedLEARN Client support team](mailto:support@succeedlearn.com) to get these values. You can also refer to the patterns shown in the **Basic SAML Configuration** section in the Microsoft Entra admin center.

6. succeedLEARN application expects the SAML assertions in a specific format, which requires you to add custom attribute mappings to your SAML token attributes configuration. The following screenshot shows the list of default attributes.

   ![Default attributes](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/media/tutorial_general_default.png)

7. In addition to above, succeedLEARN application expects few more attributes to be passed back in SAML response which are shown below. These attributes are also pre-populated but you can review them as per your requirements.

   | Name | Source Attribute |
   | ---- | ---------------- |
   | email | user.mail |
   | givenname | user.givenname |
   | surname | user.surname |
   | username | user.userprincipalname |

8. On the **Set up single sign-on with SAML** page, in the **SAML Signing Certificate** section, find **Certificate (Base64)** and select **Download** to download the certificate and save it on your computer.

   ![The Certificate download link](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/media/tutorial_general_certificate.png)

9. On the **Set up succeedLEARN** section, copy the appropriate URL(s) based on your requirement.

   ![Copy configuration URLs](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/media/tutorial_general_configure.png)

### Create a Microsoft Entra test user

In this section, you'll create a test user in the Microsoft Entra admin center called B.Simon.

1. From the left pane in the Microsoft Entra admin center, select **Identity** > **Users** > **All users**.
2. Select **New user** > **Create new user** at the top of the screen.
3. In the **User** properties, follow these steps:
   1. In the **Display name** field, enter `B.Simon`.  
   2. In the **User principal name** field, enter the username@companydomain.extension. For example, `B.Simon@contoso.com`.
   3. Select the **Show password** check box, and then write down the value that's displayed in the **Password** box.
   4. Select **Review + create**.
4. Select **Create**.

### Assign the Microsoft Entra test user

In this section, you'll enable B.Simon to use single sign-on by granting access to succeedLEARN.

1. In the Microsoft Entra admin center, select **Enterprise Applications** > **All applications**.
2. In the applications list, select **succeedLEARN**.
3. In the app's overview page, find the **Manage** section and select **Users and groups**.
4. Select **Add user**, then select **Users and groups** in the **Add Assignment** dialog.
5. In the **Users and groups** dialog, select **B.Simon** from the Users list, then click the **Select** button at the bottom of the screen.
6. If you are expecting a role to be assigned to the users, you can select it from the **Select a role** dropdown. If no role has been set up for this app, you see "Default Access" role selected.
7. In the **Add Assignment** dialog, click the **Assign** button.

### Configure succeedLEARN SSO

To configure single sign-on on **succeedLEARN** side, you need to send the downloaded **Certificate (Base64)** and appropriate copied URLs from Microsoft Entra admin center to succeedLEARN support team. They set this setting to have the SAML SSO connection set properly on both sides.

Alternatively, if you have administrator access to your succeedLEARN instance, you can configure SAML SSO manually:

1. Log in to your succeedLEARN instance as an administrator.
2. Navigate to **Site administration** > **Plugins** > **Authentication** > **SAML2**.
3. Configure the following settings:

   | Field | Value |
   |-------|-------|
   | IdP metadata URL | Use the **Login URL** from Microsoft Entra |
   | IdP certificate | Upload the **Certificate (Base64)** downloaded from Microsoft Entra |
   | SP entity ID | `https://<SUBDOMAIN>.succeedlearn.com/auth/saml2/sp/metadata.php` |
   | ACS URL | `https://<SUBDOMAIN>.succeedlearn.com/auth/saml2/sp/saml2-acs.php/succeedlearn` |

4. Configure attribute mappings:
   - **Username**: `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name`
   - **Email**: `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress`
   - **First name**: `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname`
   - **Last name**: `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname`

5. Enable **Just-in-time provisioning** if desired.
6. Save the configuration.

### Create succeedLEARN test user

In this section, a user called B.Simon is created in succeedLEARN. succeedLEARN supports just-in-time user provisioning, which is enabled by default. There is no action item for you in this section. If a user doesn't already exist in succeedLEARN, a new one is created after authentication.

### Test SSO

In this section, you test your Microsoft Entra single sign-on configuration with following options.

#### SP initiated:

1. Click on **Test this application** in Microsoft Entra admin center. This will redirect to succeedLEARN Sign-on URL where you can initiate the login flow.
2. Go to succeedLEARN Sign-on URL directly and initiate the login flow from there.

#### IdP initiated:

Click on **Test this application** in Microsoft Entra admin center and you should be automatically signed in to the succeedLEARN for which you set up the SSO.

You can also use Microsoft My Apps to test the application in any mode. When you click the succeedLEARN tile in the My Apps, if configured in SP mode you would be redirected to the application sign-on page for initiating the login flow and if configured in IdP mode, you should be automatically signed in to the succeedLEARN for which you set up the SSO. For more information about the My Apps, see [Introduction to the My Apps](../user-help/my-apps-portal-end-user-access.md).

---

## Part 2: Configure Automatic User Provisioning to succeedLEARN

This section guides you through the steps to configure the Microsoft Entra provisioning service to create, update, and disable users and groups in succeedLEARN based on user and group assignments in Microsoft Entra ID.

### Introduction to SCIM in succeedLEARN

succeedLEARN supports System for Cross-domain Identity Management (SCIM) 2.0 protocol for automated user provisioning. The SCIM implementation in succeedLEARN provides:

- **User lifecycle management**: Create, update, and disable users
- **Group management**: Create and manage group memberships
- **Real-time synchronization**: Changes are reflected immediately
- **Secure authentication**: Bearer token-based authentication

#### Supported SCIM Features

| Feature | Supported |
|---------|-----------|
| Create users | ✓ |
| Update users | ✓ |
| Disable/Enable users | ✓ |
| Delete users | ✓ |
| Create groups | ✓ |
| Update groups | ✓ |
| Group membership management | ✓ |

### Prerequisites

The scenario outlined in this tutorial assumes that you already have the following prerequisites:

- A Microsoft Entra tenant with Premium license
- A succeedLEARN tenant configured for SCIM provisioning
- Administrator permissions in both systems

### Step 1: Plan your provisioning deployment

1. Learn about [how the provisioning service works](../app-provisioning/user-provisioning.md).
2. Determine who will be in [scope for provisioning](../app-provisioning/define-conditional-rules-for-provisioning-user-accounts.md).
3. Determine what data to [map between Microsoft Entra ID and succeedLEARN](../app-provisioning/customize-application-attributes.md).

### Step 2: Configure succeedLEARN to support provisioning with Microsoft Entra ID

1. Log in to your succeedLEARN instance as an administrator.
2. Navigate to **Site administration** > **Plugins** > **Web services**.
3. Enable the SCIM web service:
   - Go to **External services** > **SCIM User Provisioning**
   - Enable the service
   - Configure the following settings:

#### SCIM Configuration Settings

| Setting | Value | Description |
|---------|-------|-------------|
| SCIM Endpoint URL | `https://<SUBDOMAIN>.succeedlearn.com/webservice/scim/v2/` | The base URL for SCIM operations |
| Authentication Method | Bearer Token | Token-based authentication |
| Token Expiration | 365 days | Recommended token lifetime |

4. Generate a SCIM Bearer Token:
   - Click **Generate New Token**
   - Copy and securely store the token (it will only be shown once)
   - The token format: `Bearer <random-string>`

5. Configure supported attributes:
   - **userName** (required): Unique identifier for the user
   - **name.givenName**: User's first name
   - **name.familyName**: User's last name
   - **emails[primary eq true].value**: Primary email address
   - **groups**: Group memberships
   - **active**: User account status

### Step 3: Add succeedLEARN from the Microsoft Entra application gallery

Add succeedLEARN from the Microsoft Entra application gallery to start managing provisioning to succeedLEARN. If you have previously set up succeedLEARN for SSO, you can use the same application. However, it's recommended that you create a separate app when testing the integration initially. [Learn more about adding an application from the gallery here](../manage-apps/add-application-portal.md).

### Step 4: Define who will be provisioned

The Microsoft Entra provisioning service allows you to scope who will be provisioned based on assignment to the application and or based on attributes of the user and group. If you choose to scope who will be provisioned to your app based on assignment, you can use the [steps](../manage-apps/assign-user-or-group-access-portal.md) to assign users and groups to the application. If you choose to scope who will be provisioned based solely on attributes of the user or group, you can use a scoping filter as described [here](../app-provisioning/define-conditional-rules-for-provisioning-user-accounts.md).

- Start small. Test with a small set of users and groups before rolling out to everyone. When scope for provisioning is set to assigned users and groups, you can control provisioning by assigning one or two users or groups to the app. When scope is set to all users and groups, you can specify an [attribute-based scoping filter](../app-provisioning/define-conditional-rules-for-provisioning-user-accounts.md).
- If you need additional roles, you can [update the application manifest](../develop/howto-add-app-roles-in-azure-ad-apps.md) to add new roles.

### Step 5: Configure automatic user provisioning to succeedLEARN

This section guides you through the steps to configure the Microsoft Entra provisioning service to create, update, and disable users and/or groups in succeedLEARN based on user and/or group assignments in Microsoft Entra ID.

#### To configure automatic user provisioning for succeedLEARN in Microsoft Entra ID:

1. Sign in to the [Microsoft Entra admin center](https://entra.microsoft.com) as at least a [Cloud Application Administrator](../identity/role-based-access-control/permissions-reference.md#cloud-application-administrator).
2. Select **Identity** > **Applications** > **Enterprise applications**.
3. Select **succeedLEARN** from the applications list.
4. Select the **Provisioning** tab.
5. Select **Get started**.
6. On the **Provisioning** page, select **Automatic**.

   ![Provisioning page](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/media/tutorial_general_provisioning.png)

7. Under the **Admin Credentials** section, input your succeedLEARN **Tenant URL** and **Secret Token**. Click **Test Connection** to ensure Microsoft Entra ID can connect to succeedLEARN. If the connection fails, ensure your succeedLEARN account has Admin permissions and try again.

   | Field | Value |
   |-------|-------|
   | Tenant URL | `https://<SUBDOMAIN>.succeedlearn.com/webservice/scim/v2/` |
   | Secret Token | The Bearer token generated in Step 2 |

   ![Admin credentials](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/media/tutorial_general_admin_credentials.png)

8. In the **Notification Email** field, enter the email address of a person or group who should receive the provisioning error notifications and select the **Send an email notification when a failure occurs** check box.

   ![Notification Email](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/media/tutorial_general_notification.png)

9. Select **Save**.

10. Under the **Mappings** section, select **Synchronize Microsoft Entra users to succeedLEARN**.

    ![User mappings](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/media/tutorial_general_user_mappings.png)

11. Review the user attributes that are synchronized from Microsoft Entra ID to succeedLEARN in the **Attribute Mapping** section. The attributes selected as **Matching** properties are used to match the user accounts in succeedLEARN for update operations. Select the **Save** button to commit any changes.

    | Microsoft Entra Attribute | succeedLEARN Attribute | Matching |
    |---------------------------|------------------------|----------|
    | userPrincipalName | userName | Yes |
    | givenName | name.givenName | No |
    | surname | name.familyName | No |
    | mail | emails[primary eq true].value | No |
    | accountEnabled | active | No |

12. Under the **Mappings** section, select **Synchronize Microsoft Entra groups to succeedLEARN**.

    ![Group mappings](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/media/tutorial_general_group_mappings.png)

13. Review the group attributes that are synchronized from Microsoft Entra ID to succeedLEARN in the **Attribute Mapping** section. The attributes selected as **Matching** properties are used to match the groups in succeedLEARN for update operations. Select the **Save** button to commit any changes.

    | Microsoft Entra Attribute | succeedLEARN Attribute | Matching |
    |---------------------------|------------------------|----------|
    | displayName | displayName | Yes |
    | members | members | No |

14. To configure scoping filters, refer to the following instructions provided in the [Scoping filter tutorial](../app-provisioning/define-conditional-rules-for-provisioning-user-accounts.md).

15. To enable the Microsoft Entra provisioning service for succeedLEARN, change the **Provisioning Status** to **On** in the **Settings** section.

    ![Provisioning Status Toggled On](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/media/tutorial_general_provisioning_on.png)

16. Define the users and/or groups that you would like to provision to succeedLEARN by choosing the desired values in **Scope** in the **Settings** section.

    ![Provisioning Scope](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/media/tutorial_general_provisioning_scope.png)

17. When you are ready to provision, click **Save**.

    ![Saving Provisioning Configuration](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/media/tutorial_general_provisioning_save.png)

This operation starts the initial synchronization cycle of all users and groups defined in **Scope** in the **Settings** section. The initial cycle takes longer to perform than subsequent cycles, which occur approximately every 40 minutes as long as the Microsoft Entra provisioning service is running.

### Step 6: Monitor your deployment

Once you've configured provisioning, use the following resources to monitor your deployment:

- Use the [provisioning logs](../reports-monitoring/concept-provisioning-logs.md) to determine which users have been provisioned successfully or unsuccessfully
- Check the [progress bar](../app-provisioning/application-provisioning-when-will-provisioning-finish-specific-user.md) to see the status of the provisioning cycle and how close it is to completion
- If the provisioning configuration seems to be in an unhealthy state, the application will go into quarantine. [Learn more about quarantine states here](../app-provisioning/application-provisioning-quarantine-status.md)

---

## Troubleshooting

### Common SSO Issues

#### Error: "AADSTS50011: The reply url specified in the request does not match the reply urls configured for the application"

**Cause**: The Reply URL in Microsoft Entra ID doesn't match the ACS URL configured in succeedLEARN.

**Solution**:
1. Verify the Reply URL in Microsoft Entra ID matches: `https://<SUBDOMAIN>.succeedlearn.com/auth/saml2/sp/saml2-acs.php/succeedlearn`
2. Check that the subdomain is correct
3. Ensure there are no trailing slashes or typos

#### Error: "AADSTS90022: The request body must contain the following parameter: 'client_assertion' or 'client_secret'"

**Cause**: Certificate configuration issue.

**Solution**:
1. Re-download the Certificate (Base64) from Microsoft Entra ID
2. Upload the certificate to succeedLEARN SAML configuration
3. Ensure the certificate hasn't expired

### Common Provisioning Issues

#### Error: "Invalid credentials"

**Cause**: The Bearer token is incorrect or expired.

**Solution**:
1. Regenerate the SCIM Bearer token in succeedLEARN
2. Update the Secret Token in Microsoft Entra provisioning configuration
3. Test the connection again

#### Error: "User creation failed: Username already exists"

**Cause**: The username mapping is creating duplicate usernames.

**Solution**:
1. Review the username attribute mapping
2. Consider using `mail` instead of `userPrincipalName` for username mapping
3. Enable "Update matching users" in provisioning settings

#### Error: "SCIM endpoint returned HTTP 404"

**Cause**: SCIM endpoint URL is incorrect or SCIM service is disabled.

**Solution**:
1. Verify the SCIM endpoint URL: `https://<SUBDOMAIN>.succeedlearn.com/webservice/scim/v2/`
2. Check that SCIM web service is enabled in succeedLEARN
3. Verify the subdomain is correct

### Provisioning Logs Analysis

To analyze provisioning logs:

1. Go to **Microsoft Entra admin center** > **Identity** > **Applications** > **Enterprise applications**
2. Select your succeedLEARN application
3. Navigate to **Provisioning** > **Provisioning logs**
4. Look for:
   - **Success**: Users/groups successfully created or updated
   - **Failure**: Failed operations with error details
   - **Skipped**: Users/groups that were skipped due to scoping rules

### Testing Checklist

Before going live, test the following scenarios:

#### SSO Testing
- [ ] SP-initiated login works
- [ ] IdP-initiated login works
- [ ] User attributes are correctly mapped
- [ ] Just-in-time provisioning creates users (if enabled)
- [ ] Logout works properly

#### Provisioning Testing
- [ ] New users are created in succeedLEARN
- [ ] User updates are synchronized
- [ ] User deactivation works
- [ ] Group membership is synchronized
- [ ] Group creation and updates work

---

## Support

### Contact Information

For technical support with succeedLEARN integration:

- **Email**: [support@succeedlearn.com](mailto:support@succeedlearn.com)
- **Support Portal**: https://support.succeedlearn.com
- **Documentation**: https://docs.succeedlearn.com

### Service Level Agreement (SLA)

- **Response Time**: 
  - Critical issues: 4 hours
  - High priority: 1 business day
  - Medium priority: 2 business days
  - Low priority: 5 business days

- **Resolution Time**:
  - Critical issues: 24 hours
  - High priority: 3 business days
  - Medium priority: 1 week
  - Low priority: 2 weeks

### Support Requirements

When contacting support, please provide:

1. **Environment details**: 
   - succeedLEARN version
   - Microsoft Entra tenant ID
   - Application ID

2. **Issue description**:
   - Steps to reproduce
   - Expected vs. actual behavior
   - Screenshots or error messages

3. **Log files**:
   - SAML authentication logs
   - Provisioning logs
   - succeedLEARN error logs

### Escalation Process

1. **Level 1**: Technical Support Team
2. **Level 2**: Senior Technical Support
3. **Level 3**: Engineering Team
4. **Level 4**: Product Management

---

## Additional Notes

### Multiple Instances Support

succeedLEARN supports **multiple instances per Microsoft Entra tenant**. This means you can:

- Configure multiple succeedLEARN applications in the same tenant
- Use different Identifier URLs for each instance
- Maintain separate user provisioning for each instance

To configure multiple instances:
1. Add each succeedLEARN instance as a separate application from the gallery
2. Use unique Identifier URLs: `https://<UNIQUE-SUBDOMAIN>.succeedlearn.com/auth/saml2/sp/metadata.php`
3. Configure SCIM endpoints for each instance separately

### License Requirements

- **Microsoft Entra ID**: Premium P1 or P2 license required for SSO and automated provisioning
- **succeedLEARN**: Enterprise license required for SAML SSO and SCIM provisioning features

### Security Considerations

1. **Certificate Management**: 
   - Monitor certificate expiration dates
   - Set up alerts 30 days before expiration
   - Use strong certificates (minimum 2048-bit RSA)

2. **Token Security**:
   - Rotate SCIM Bearer tokens annually
   - Store tokens securely
   - Monitor token usage in logs

3. **Network Security**:
   - Ensure HTTPS is used for all communications
   - Consider IP restrictions for SCIM endpoints
   - Monitor failed authentication attempts

---

## Next Steps

After completing this integration:

1. **Monitor**: Set up monitoring and alerting for SSO and provisioning
2. **Scale**: Gradually roll out to more users and groups
3. **Optimize**: Fine-tune attribute mappings based on user feedback
4. **Update**: Keep certificates and configurations up to date

For more information about Microsoft Entra ID integrations, see:
- [Microsoft Entra documentation](https://docs.microsoft.com/en-us/azure/active-directory/)
- [SaaS app integration tutorials](https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/tutorial-list) 